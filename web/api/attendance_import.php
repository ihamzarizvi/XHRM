<?php
/**
 * ZKTeco Attendance Import API
 * ============================
 * Receives attendance data from the ZKTeco sync tool and imports into XHRM.
 * Supports both JSON (API mode) and CSV file upload.
 *
 * Security: Protected by API key.
 *
 * Endpoints:
 *   POST /web/api/attendance_import.php
 *     - JSON mode: {"api_key": "...", "records": [...]}
 *     - CSV mode:  multipart form with attendance_file + api_key
 *     - Test mode: {"api_key": "...", "action": "test"}
 */

// ─── Bootstrap XHRM ──────────────────────────────────────────────────────────
require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\ORM\EntityManager;

// Load .env or config
$configFile = __DIR__ . '/../lib/confs/Conf.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

// ─── Configuration ────────────────────────────────────────────────────────────
// IMPORTANT: Change this to a strong, unique key and keep it in sync with config.ini
define('API_KEY', 'xhrm-zkteco-sync-2024-secret-key');

// ─── Headers ──────────────────────────────────────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ─── Database Connection ──────────────────────────────────────────────────────
function getDbConnection()
{
    // Try to use XHRM's existing database configuration
    $configFile = __DIR__ . '/../lib/confs/Conf.php';
    if (file_exists($configFile)) {
        require_once $configFile;
        if (class_exists('Conf')) {
            $conf = new Conf();
            $dbHost = $conf->getDbHost();
            $dbPort = $conf->getDbPort();
            $dbName = $conf->getDbName();
            $dbUser = $conf->getDbUser();
            $dbPass = $conf->getDbPass();
        }
    }

    // Fallback: try environment or direct config
    if (empty($dbHost)) {
        // Read from .env or hardcode for now
        $dbHost = getenv('DB_HOST') ?: 'localhost';
        $dbPort = getenv('DB_PORT') ?: '3306';
        $dbName = getenv('DB_NAME') ?: 'xhrm';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPass = getenv('DB_PASS') ?: '';
    }

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// ─── Auth ─────────────────────────────────────────────────────────────────────
function validateApiKey($key)
{
    return $key === API_KEY;
}

// ─── Import Logic ─────────────────────────────────────────────────────────────
function importRecords($pdo, $records)
{
    $results = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => []
    ];

    foreach ($records as $record) {
        $empNumber = intval($record['empNumber'] ?? 0);
        $date = $record['date'] ?? '';
        $punchInStr = $record['punchIn'] ?? '';
        $punchOutStr = $record['punchOut'] ?? null;
        $timezoneName = $record['timezoneName'] ?? 'Asia/Karachi';
        $timezoneOffset = floatval($record['timezoneOffset'] ?? 5.0);

        if (empty($empNumber) || empty($punchInStr)) {
            $results['errors'][] = "Missing empNumber or punchIn for record";
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

            // Check for existing record on same date for this employee
            $dateStart = (clone $punchInUtc)->setTime(0, 0, 0)->format('Y-m-d H:i:s');
            $dateEnd = (clone $punchInUtc)->setTime(23, 59, 59)->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare("
                SELECT id FROM ohrm_attendance_record
                WHERE employee_id = ?
                AND punch_in_utc_time BETWEEN ? AND ?
            ");
            $stmt->execute([$empNumber, $dateStart, $dateEnd]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing record with punch-out if available
                if ($punchOutStr) {
                    $punchOutDt = new DateTime($punchOutStr, new DateTimeZone($timezoneName));
                    $punchOutUtc = clone $punchOutDt;
                    $punchOutUtc->setTimezone(new DateTimeZone('UTC'));

                    $stmt = $pdo->prepare("
                        UPDATE ohrm_attendance_record
                        SET punch_out_utc_time = ?,
                            punch_out_user_time = ?,
                            punch_out_time_offset = ?,
                            punch_out_timezone_name = ?,
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

            // Insert new punch-in record
            $state = $punchOutStr ? 'PUNCHED OUT' : 'PUNCHED IN';

            $stmt = $pdo->prepare("
                INSERT INTO ohrm_attendance_record
                (employee_id, punch_in_utc_time, punch_in_user_time, punch_in_time_offset,
                 punch_in_timezone_name, punch_in_note, state,
                 punch_out_utc_time, punch_out_user_time, punch_out_time_offset,
                 punch_out_timezone_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

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
    $results = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => []
    ];

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        return ['imported' => 0, 'errors' => ['Cannot open uploaded file']];
    }

    // Read header
    $header = fgetcsv($handle);
    $idxNo = array_search('No.', $header);
    $idxDateTime = array_search('Date/Time', $header);

    if ($idxNo === false || $idxDateTime === false) {
        return ['imported' => 0, 'errors' => ['Invalid CSV format. Expected: Name, No., Date/Time, ...']];
    }

    // Group punches by employee and date
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

    // Convert to records format
    $records = [];
    foreach ($rawPunches as $empId => $dates) {
        foreach ($dates as $dateKey => $punches) {
            usort($punches, function ($a, $b) {
                return $a <=> $b; });
            $record = [
                'empNumber' => intval($empId),
                'date' => $dateKey,
                'punchIn' => $punches[0]->format('Y-m-d H:i:s'),
                'timezoneName' => $timezoneName,
                'timezoneOffset' => $timezoneOffset,
            ];
            if (count($punches) > 1) {
                $record['punchOut'] = end($punches)->format('Y-m-d H:i:s');
            }
            $records[] = $record;
        }
    }

    return importRecords($pdo, $records);
}

// ─── Main Handler ─────────────────────────────────────────────────────────────
try {
    // Determine mode: JSON or CSV
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        // JSON API mode
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !validateApiKey($input['api_key'] ?? '')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid API key']);
            exit;
        }

        // Test mode
        if (($input['action'] ?? '') === 'test') {
            $pdo = getDbConnection();
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM hs_hr_employee");
            $count = $stmt->fetch()['cnt'];
            echo json_encode([
                'success' => true,
                'message' => "API connected. {$count} employees in database."
            ]);
            exit;
        }

        // Import mode
        $records = $input['records'] ?? [];
        if (empty($records)) {
            echo json_encode(['success' => false, 'message' => 'No records provided']);
            exit;
        }

        $pdo = getDbConnection();
        $results = importRecords($pdo, $records);
        echo json_encode([
            'success' => true,
            'imported' => $results['imported'],
            'skipped' => $results['skipped'],
            'errors' => $results['errors']
        ]);

    } elseif (isset($_FILES['attendance_file'])) {
        // CSV upload mode
        $apiKey = $_POST['api_key'] ?? '';
        if (!validateApiKey($apiKey)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid API key']);
            exit;
        }

        $timezoneName = $_POST['timezone_name'] ?? 'Asia/Karachi';
        $timezoneOffset = floatval($_POST['timezone_offset'] ?? 5.0);

        $pdo = getDbConnection();
        $results = importCsvFile($pdo, $_FILES['attendance_file'], $timezoneName, $timezoneOffset);
        echo json_encode([
            'success' => true,
            'imported' => $results['imported'],
            'skipped' => $results['skipped'],
            'errors' => $results['errors']
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'No data received. Send JSON or CSV file.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
