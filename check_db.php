<?php
// check_db.php - Place in XHRM ROOT directory (e.g., public_html/ or web/)

define('ROOT_PATH', __DIR__);

// Try to locate config
$configFile = null;
if (file_exists(ROOT_PATH . '/lib/confs/Conf.php')) {
    $configFile = ROOT_PATH . '/lib/confs/Conf.php';
} elseif (file_exists(ROOT_PATH . '/src/lib/confs/Conf.php')) {
    $configFile = ROOT_PATH . '/src/lib/confs/Conf.php';
} elseif (file_exists(ROOT_PATH . '/symfony/apps/orangehrm/lib/confs/Conf.php')) {
    $configFile = ROOT_PATH . '/symfony/apps/orangehrm/lib/confs/Conf.php';
}

if ($configFile) {
    echo "Found config: $configFile<br>";
    include_once($configFile);
} else {
    die("Error: Could not find lib/confs/Conf.php. Please verify file structure.");
}

$conf = new Conf();
echo "Connecting to DB: " . $conf->dbname . "<br>";

try {
    $dsn = "mysql:host={$conf->dbhost};dbname={$conf->dbname}";
    $pdo = new PDO($dsn, $conf->dbuser, $conf->dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2 style='color:green'>Database Connection Successful</h2>";
} catch (PDOException $e) {
    die("<h2 style='color:red'>Connection Failed: " . $e->getMessage() . "</h2>");
}

echo "<h3>1. Checking Tables</h3>";
$tables = ['ohrm_vault_item', 'ohrm_vault_category', 'ohrm_vault_user_key', 'ohrm_vault_share', 'ohrm_user_role_screen'];
foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM $t LIMIT 1");
        echo "Table <b>$t</b>: <span style='color:green'>EXISTS</span><br>";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (strpos($msg, "doesn't exist") !== false) {
            echo "Table <b>$t</b>: <span style='color:red; font-weight:bold'>MISSING (Run SQL!)</span><br>";
        } else {
            echo "Table <b>$t</b>: <span style='color:orange'>Error ($msg)</span><br>";
        }
    }
}

echo "<h3>2. Checking Critical Columns (ohrm_vault_item)</h3>";
$columns = ['password_strength', 'breach_detected', 'last_accessed', 'password_last_changed'];
foreach ($columns as $c) {
    try {
        $pdo->query("SELECT $c FROM ohrm_vault_item LIMIT 1");
        echo "Column <b>$c</b>: <span style='color:green'>EXISTS</span><br>";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (strpos($msg, "Unknown column") !== false) {
            echo "Column <b>$c</b>: <span style='color:red; font-weight:bold'>MISSING (Run SQL!)</span><br>";
        } else {
            echo "Column <b>$c</b>: <span style='color:orange'>Error ($msg)</span><br>";
        }
    }
}


echo "<h3>3. Checking Permissions (Role: Admin)</h3>";
try {
    echo "Querying permissions...<br>";
    // Look for Role 'Admin' ID
    $roleStmt = $pdo->query("SELECT id FROM ohrm_user_role WHERE name='Admin'");
    $roleId = $roleStmt->fetchColumn();

    if (!$roleId) {
        echo "Role 'Admin': <span style='color:red'>NOT FOUND</span><br>";
    } else {
        echo "Role 'Admin' ID: $roleId <span style='color:green'>Found</span><br>";

        // Check screens
        $screens = ['View Password Vault', 'Password Manager Admin'];
        foreach ($screens as $screenName) {
            $screenStmt = $pdo->prepare("SELECT id FROM ohrm_screen WHERE name = ?");
            $screenStmt->execute([$screenName]);
            $screenId = $screenStmt->fetchColumn();

            if (!$screenId) {
                echo "Screen '$screenName': <span style='color:red'>MISSING</span><br>";
                continue;
            }

            $permStmt = $pdo->prepare("SELECT can_read FROM ohrm_user_role_screen WHERE user_role_id = ? AND screen_id = ?");
            $permStmt->execute([$roleId, $screenId]);
            $canRead = $permStmt->fetchColumn();

            if ($canRead) {
                echo "Permission '$screenName': <span style='color:green'>GRANTED</span><br>";
            } else {
                echo "Permission '$screenName': <span style='color:red'>DENIED/MISSING</span> (Check SQL)<br>";
            }
        }
    }

} catch (Exception $e) {
    echo "Error checking permissions: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Checking Backend Code (File Check)</h3>";

$pluginDir = ROOT_PATH . '/src/plugins/XHRMPasswordManagerPlugin';
if (!is_dir($pluginDir)) {
    echo "Plugin Dir: <span style='color:red'>NOT FOUND at $pluginDir</span><br>";
} else {
    echo "Plugin Dir: <span style='color:green'>Found</span><br>";

    // Check Routes
    $routesFile = $pluginDir . '/config/routes.yaml';
    if (file_exists($routesFile)) {
        $content = file_get_contents($routesFile);
        if (strpos($content, 'xhrm_password_manager_api_shares') !== false) {
            echo "Routes (Shares Fix): <span style='color:green'>PRESENT</span><br>";
        } else {
            echo "Routes (Shares Fix): <span style='color:red'>MISSING (Update Code!)</span><br>";
        }
    } else {
        echo "routes.yaml: <span style='color:red'>MISSING</span><br>";
    }

    // Check Service
    $serviceFile = $pluginDir . '/Service/VaultPermissionService.php';
    if (file_exists($serviceFile)) {
        $content = file_get_contents($serviceFile);
        if (strpos($content, 'AccessDeniedHttpException') !== false) {
            echo "Service (Exception Fix): <span style='color:green'>UPDATED</span><br>";
        } else {
            echo "Service (Exception Fix): <span style='color:red'>OLD CODE DETECTED (Update Code!)</span><br>";
        }
    } else {
        echo "VaultPermissionService.php: <span style='color:red'>MISSING</span><br>";
    }
}
