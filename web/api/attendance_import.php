<?php
/**
 * ZKTeco Attendance Import API
 * ============================
 * Receives attendance data from the ZKTeco sync tool and imports into XHRM.
 * Supports both JSON (API mode) and CSV file upload.
 *
 * Security: Protected by API key.
 */

// ─── Configuration ────────────────────────────────────────────────────────────
define('API_KEY', 'xhrm-zkteco-sync-2024-secret-key');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ─── Database Connection (same logic as run_sql.php) ──────────────────────────
function getDbConnection()
{
    // web/api/ is 2 levels deep from project root
    $projectRoot = dirname(__DIR__, 2);  // go up from web/api → web → project root
    $possibleConfs = [
        $projectRoot . '/lib/confs/Conf.php',
        $projectRoot . '/config/Conf.php',
    ];

    $dbHost = 'localhost';
    $dbPort = '3306';
    $dbName = $dbUser = $dbPass = '';

    // Try Conf.php (regex parse — doesn't require class loading)
    foreach ($possibleConfs as $f) {
        if (file_exists($f)) {
            $content = file_get_contents($f);
            if (preg_match("/dbhost\s*=\s*['\"]([^'\"]+)/", $content, $m))
                $dbHost = $m[1];
            if (preg_match("/dbport\s*=\s*['\"]([^'\"]+)/", $content, $m))
                $dbPort = $m[1];
            if (preg_match("/dbname\s*=\s*['\"]([^'\"]+)/", $content, $m))
                $dbName = $m[1];
            if (preg_match("/dbuser\s*=\s*['\"]([^'\"]+)/", $content, $m))
                $dbUser = $m[1];
            if (preg_match("/dbpass\s*=\s*['\"]([^'\"]*)/", $content, $m))
                $dbPass = $m[1];
            break;
        }
    }

    // Fallback: .env file
    if (!$dbName) {
        $envPaths = [
            $projectRoot . '/.env',
            $projectRoot . '/.env.local',
        ];
        foreach ($envPaths as $ef) {
            if (file_exists($ef)) {
                foreach (file($ef) as $line) {
                    $line = trim($line);
                    if (preg_match('/^DATABASE_URL=mysql:\/\/([^:]+):([^@]*)@([^:\/]+)[^\/]*\/([^\?]+)/', $line, $m)) {
                        $dbUser = urldecode($m[1]);
                        $dbPass = urldecode($m[2]);
                        $dbHost = $m[3];
                        $dbName = $m[4];
                        break 2;
                    }
                }
            }
        }
    }

    if (!$dbName) {
        throw new Exception("Cannot find database configuration");
    }

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

// ─── Import Logic ─────────────────────────────────────────────────────────────
function importRecords($pdo, $records)
{
    $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];

    foreach ($records as $record) {
        $empNumber = intval($record['empNumber'] ?? 0);
        $punchInStr = $record['punchIn'] ?? '';
        $punchOutStr = $record['punchOut'] ?? null;
        $timezoneName = $record['timezoneName'] ?? 'Asia/Karachi';
        $timezoneOffset = floatval($record['timezoneOffset'] ?? 5.0);

        if (empty($empNumber) || empty($punchInStr)) {
            $results['errors'][] = "Missing empNumber or punchIn";
            continue;
        }

        // Verify employee exists
        $stmt = $pdo->prepare("SELECT emp_number FROM hs_hr_employee WHERE emp_number = ?");
        $stmt->execute([$empNumber]);
        if (!$stmt->fetch()) {
            $results['errors'][] = "Employee #{$empNumber} not found";
            continue;
        }

        try {
            $punchInDt = new DateTime($punchInStr, new DateTimeZone($timezoneName));
            $punchInUtc = clone $punchInDt;
            $punchInUtc->setTimezone(new DateTimeZone('UTC'));

            // Check for existing record on same date
            $dateStr = $punchInDt->format('Y-m-d');
            $stmt = $pdo->prepare("
                SELECT id FROM ohrm_attendance_record
                WHERE employee_id = ?
                AND DATE(punch_in_user_time) = ?
                LIMIT 1
            ");
            $stmt->execute([$empNumber, $dateStr]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing record with punch-out if available
                if ($punchOutStr) {
                    $punchOutDt = new DateTime($punchOutStr, new DateTimeZone($timezoneName));
                    $punchOutUtc = clone $punchOutDt;
                    $punchOutUtc->setTimezone(new DateTimeZone('UTC'));

                    $stmt = $pdo->prepare("
                        UPDATE ohrm_attendance_record
                        SET punch_out_utc_time = ?, punch_out_user_time = ?,
                            punch_out_time_offset = ?, punch_out_timezone_name = ?,
                            state = 'PUNCHED OUT'
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $punchOutUtc->format('Y-m-d H:i:s'),
                        $punchOutDt->format('Y-m-d H:i:s'),
                        $timezoneOffset,
                        $timezoneName,
                        $existing['id']
                    ]);
                    $results['imported']++;
                } else {
                    $results['skipped']++;
                }
                continue;
            }

            // Insert new record
            $state = $punchOutStr ? 'PUNCHED OUT' : 'PUNCHED IN';

            $punchOutUtcStr = null;
            $punchOutUserStr = null;
            $punchOutOffset = null;
            $punchOutTzName = null;

            if ($punchOutStr) {
                $punchOutDt = new DateTime($punchOutStr, new DateTimeZone($timezoneName));
                $punchOutUtc = clone $punchOutDt;
                $punchOutUtc->setTimezone(new DateTimeZone('UTC'));
                $punchOutUtcStr = $punchOutUtc->format('Y-m-d H:i:s');
                $punchOutUserStr = $punchOutDt->format('Y-m-d H:i:s');
                $punchOutOffset = $timezoneOffset;
                $punchOutTzName = $timezoneName;
            }

            $stmt = $pdo->prepare("
                INSERT INTO ohrm_attendance_record
                (employee_id, punch_in_utc_time, punch_in_user_time, punch_in_time_offset,
                 punch_in_timezone_name, punch_in_note, state,
                 punch_out_utc_time, punch_out_user_time, punch_out_time_offset,
                 punch_out_timezone_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $empNumber,
                $punchInUtc->format('Y-m-d H:i:s'),
                $punchInDt->format('Y-m-d H:i:s'),
                $timezoneOffset,
                $timezoneName,
                'ZKTeco Import',
                $state,
                $punchOutUtcStr,
                $punchOutUserStr,
                $punchOutOffset,
                $punchOutTzName
            ]);
            $results['imported']++;

        } catch (Exception $e) {
            $results['errors'][] = "Emp #{$empNumber}: " . $e->getMessage();
        }
    }

    return $results;
}

function importCsvFile($pdo, $file, $timezoneName, $timezoneOffset)
{
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        return ['imported' => 0, 'skipped' => 0, 'errors' => ['Cannot open file']];
    }

    $header = fgetcsv($handle);
    $idxNo = array_search('No.', $header);
    $idxDateTime = array_search('Date/Time', $header);

    if ($idxNo === false || $idxDateTime === false) {
        fclose($handle);
        return ['imported' => 0, 'skipped' => 0, 'errors' => ['Invalid CSV. Expected: Name, No., Date/Time, ...']];
    }

    // Group punches by employee+date
    $rawPunches = [];
    while (($data = fgetcsv($handle)) !== false) {
        $empId = $data[$idxNo] ?? null;
        $dtStr = $data[$idxDateTime] ?? null;
        if (empty($empId) || empty($dtStr))
            continue;

        $dt = DateTime::createFromFormat('d-m-Y H:i', $dtStr, new DateTimeZone($timezoneName));
        if ($dt) {
            $dateKey = $dt->format('Y-m-d');
            $rawPunches[$empId][$dateKey][] = $dt;
        }
    }
    fclose($handle);

    // Convert to records
    $records = [];
    foreach ($rawPunches as $empId => $dates) {
        foreach ($dates as $dateKey => $punches) {
            usort($punches, function ($a, $b) {
                return $a <=> $b;
            });
            $r = [
                'empNumber' => intval($empId),
                'date' => $dateKey,
                'punchIn' => $punches[0]->format('Y-m-d H:i:s'),
                'timezoneName' => $timezoneName,
                'timezoneOffset' => $timezoneOffset,
            ];
            if (count($punches) > 1) {
                $r['punchOut'] = end($punches)->format('Y-m-d H:i:s');
            }
            $records[] = $r;
        }
    }

    return importRecords($pdo, $records);
}

// ─── Main Handler ─────────────────────────────────────────────────────────────
try {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || ($input['api_key'] ?? '') !== API_KEY) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid API key']);
            exit;
        }

        // Test mode
        if (($input['action'] ?? '') === 'test') {
            $pdo = getDbConnection();
            $cnt = $pdo->query("SELECT COUNT(*) as c FROM hs_hr_employee")->fetch()['c'];
            echo json_encode(['success' => true, 'message' => "Connected. {$cnt} employees."]);
            exit;
        }

        $records = $input['records'] ?? [];
        if (empty($records)) {
            echo json_encode(['success' => false, 'message' => 'No records']);
            exit;
        }

        $pdo = getDbConnection();
        $results = importRecords($pdo, $records);
        echo json_encode(['success' => true] + $results);

    } elseif (isset($_FILES['attendance_file'])) {
        if (($_POST['api_key'] ?? '') !== API_KEY) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid API key']);
            exit;
        }

        $pdo = getDbConnection();
        $results = importCsvFile(
            $pdo,
            $_FILES['attendance_file'],
            $_POST['timezone_name'] ?? 'Asia/Karachi',
            floatval($_POST['timezone_offset'] ?? 5.0)
        );
        echo json_encode(['success' => true] + $results);

    } else {
        echo json_encode(['success' => false, 'message' => 'Send JSON or CSV file']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
