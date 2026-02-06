<?php
/**
 * XHRM Diagnostic & Password Reset Tool (v3)
 * 
 * IMPORTANT: DELETE THIS FILE AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Diagnostic Tool v3</h1>";

$confFile = __DIR__ . '/../lib/confs/Conf.php';
echo "Checking for config file: <code>$confFile</code>... ";

if (file_exists($confFile)) {
    echo "<span style='color:green'>Found</span><br>";
    require_once $confFile;
    $conf = new Conf();

    echo "<h2>Conf Object Methods Inspection</h2>";
    echo "Available methods: " . implode(', ', get_class_methods($conf)) . "<br>";

    echo "<h2>Database Connection Test</h2>";
    try {
        // Try getters first, then properties
        $host = method_exists($conf, 'getDbHost') ? $conf->getDbHost() : ($conf->dbhost ?? 'localhost');
        $port = method_exists($conf, 'getDbPort') ? $conf->getDbPort() : ($conf->dbport ?? '3306');
        $name = method_exists($conf, 'getDbName') ? $conf->getDbName() : ($conf->dbname ?? '');
        $user = method_exists($conf, 'getDbUser') ? $conf->getDbUser() : ($conf->dbuser ?? '');
        $pass = method_exists($conf, 'getDbPass') ? $conf->getDbPass() : ($conf->dbpass ?? '');

        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8";
        echo "DSN: $dsn (User: $user)<br>";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "<span style='color:green'>Successfully connected to the database!</span><br>";

        // Form
        echo "<h2>Reset Password</h2>";
        if (isset($_POST['reset_user']) && isset($_POST['new_password'])) {
            $username = $_POST['reset_user'];
            $newPass = $_POST['new_password'];
            $hashedPass = md5($newPass);

            $update = $pdo->prepare("UPDATE ohrm_user SET user_password = ? WHERE user_name = ?");
            $update->execute([$hashedPass, $username]);

            if ($update->rowCount() > 0) {
                echo "<p style='color:green'>Password for user <b>$username</b> has been reset successfully!</p>";
            } else {
                echo "<p style='color:red'>User <b>$username</b> not found or password unchanged.</p>";
            }
        }

        echo '<form method="POST">
            Username: <input type="text" name="reset_user" required><br>
            New Password: <input type="password" name="new_password" required><br>
            <input type="submit" value="Reset Password">
        </form>';

    } catch (Throwable $e) {
        echo "<span style='color:red'>Test Failed: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<span style='color:red'>NOT FOUND</span><br>";
}

echo "<h2>Environment Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h2>Server Logs (Last 20)</h2>";
$logFile = __DIR__ . '/../src/log/XHRM.log';
if (file_exists($logFile)) {
    echo "<pre>";
    $logs = array_slice(file($logFile), -20);
    foreach ($logs as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "No XHRM log file found.<br>";
}
echo "<hr><p style='color:red'><b>REMINDER: Delete this script!</b></p>";
