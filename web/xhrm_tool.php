<?php
/**
 * XHRM Diagnostic & Password Reset Tool (v5)
 * 
 * IMPORTANT: DELETE THIS FILE AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Diagnostic Tool v5</h1>";

$confFile = __DIR__ . '/../lib/confs/Conf.php';
$logFile = __DIR__ . '/../src/log/XHRM.log';
$proxyDir = __DIR__ . '/../src/config/proxy';

// Action: Clear Logs
if (isset($_GET['clear_logs'])) {
    if (file_exists($logFile))
        file_put_contents($logFile, "");
    header("Location: xhrm_tool.php?status=logs_cleared");
    exit;
}

// Action: Generate Proxies (Manual Trigger)
if (isset($_GET['generate_proxies'])) {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once $confFile;
        // The simple way is to just boot the entity manager
        include_once(__DIR__ . '/../src/config/log_settings.php');
        $kernel = new XHRM\Framework\Framework('prod', false);
        $em = XHRM\ORM\Doctrine::getEntityManager();
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $em->getProxyFactory()->generateProxyClasses($metadatas, $proxyDir);
        header("Location: xhrm_tool.php?status=proxies_generated");
    } catch (Throwable $e) {
        die("Error generating proxies: " . $e->getMessage());
    }
    exit;
}

if (isset($_GET['status'])) {
    echo "<p style='color:blue'>Action completed: " . htmlspecialchars($_GET['status']) . "</p>";
}

echo "Checking for config file: <code>$confFile</code>... ";

if (file_exists($confFile)) {
    echo "<span style='color:green'>Found</span><br>";
    require_once $confFile;
    $conf = new Conf();

    echo "<h2>Database Connection Test</h2>";
    try {
        $host = method_exists($conf, 'getDbHost') ? $conf->getDbHost() : ($conf->dbhost ?? '127.0.0.1');
        $port = method_exists($conf, 'getDbPort') ? $conf->getDbPort() : ($conf->dbport ?? '3306');
        $name = method_exists($conf, 'getDbName') ? $conf->getDbName() : ($conf->dbname ?? '');
        $user = method_exists($conf, 'getDbUser') ? $conf->getDbUser() : ($conf->dbuser ?? '');
        $pass = method_exists($conf, 'getDbPass') ? $conf->getDbPass() : ($conf->dbpass ?? '');

        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "<span style='color:green'>Successfully connected to the database!</span><br>";

        // Form
        echo "<h2>Reset Password</h2>";
        if (isset($_POST['reset_user']) && isset($_POST['new_password'])) {
            $username = $_POST['reset_user'];
            $newPass = $_POST['new_password'];
            $hashedPass = md5($newPass);
            $update = $pdo->prepare("UPDATE ohrm_user SET user_password = ? WHERE user_name = ?");
            $update->execute([$hashedPass, $username]);
            echo ($update->rowCount() > 0) ? "<p style='color:green'>Success!</p>" : "<p style='color:red'>User not found.</p>";
        }

        echo '<form method="POST">
            Username: <input type="text" name="reset_user" required> 
            New Password: <input type="password" name="new_password" required>
            <input type="submit" value="Reset Password">
        </form>';

    } catch (Throwable $e) {
        echo "<span style='color:red'>Test Failed: " . $e->getMessage() . "</span><br>";
    }
}

echo "<h2>Proxy Management</h2>";
if (is_dir($proxyDir)) {
    $files = array_diff(scandir($proxyDir), array('.', '..', '.gitkeep'));
    echo "Proxy Directory: <code>$proxyDir</code> - <span style='color:green'>Exists</span><br>";
    echo "Generated Files: " . count($files) . "<br>";
    echo "<a href='xhrm_tool.php?generate_proxies=1'>[ Force Generate Proxy Classes ]</a><br>";
} else {
    echo "Proxy Directory: <code>$proxyDir</code> - <span style='color:red'>MISSING</span><br>";
}

echo "<h2>Server Logs</h2>";
echo "<a href='xhrm_tool.php?clear_logs=1'>[ Clear Logs ]</a><br><br>";
if (file_exists($logFile)) {
    echo "<pre style='background:#eee; padding:10px; border:1px solid #ccc; max-height:400px; overflow:auto;'>";
    $lines = file($logFile);
    if (empty($lines)) {
        echo "Log is empty.";
    } else {
        $logs = array_slice($lines, -50);
        foreach ($logs as $line) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
}

echo "<hr><p style='color:red'><b>REMINDER: Delete this script!</b></p>";
