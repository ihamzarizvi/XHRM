<?php
/**
 * XHRM Diagnostic & Password Reset Tool
 * 
 * IMPORTANT: DELETE THIS FILE AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Diagnostic Tool</h1>";

$confFile = __DIR__ . '/../lib/confs/Conf.php';
echo "Checking for config file: <code>$confFile</code>... ";

if (file_exists($confFile)) {
    echo "<span style='color:green'>Found</span><br>";
    require_once $confFile;
    $conf = new Conf();

    echo "<h2>Database Connection Test</h2>";
    try {
        $dsn = "mysql:host={$conf->dbhost};port={$conf->dbport};dbname={$conf->dbname};charset=utf8";
        $pdo = new PDO($dsn, $conf->dbuser, $conf->dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "<span style='color:green'>Successfully connected to the database!</span><br>";

        // Check ohrm_user table
        $stmt = $pdo->query("SELECT id, user_name, status, deleted FROM ohrm_user LIMIT 10");
        $users = $stmt->fetchAll();

        echo "<h3>Existing Users (Last 10)</h3>";
        if (count($users) > 0) {
            echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Status</th><th>Deleted</th></tr>";
            foreach ($users as $user) {
                echo "<tr><td>{$user['id']}</td><td>{$user['user_name']}</td><td>{$user['status']}</td><td>{$user['deleted']}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "No users found in ohrm_user table.<br>";
        }

        // Password Reset Form
        echo "<h2>Reset Password</h2>";
        if (isset($_POST['reset_user']) && isset($_POST['new_password'])) {
            $username = $_POST['reset_user'];
            $newPass = $_POST['new_password'];
            $hashedPass = md5($newPass); // Legacy fallback supported by XHRM

            $update = $pdo->prepare("UPDATE ohrm_user SET user_password = ? WHERE user_name = ?");
            $update->execute([$hashedPass, $username]);

            if ($update->rowCount() > 0) {
                echo "<p style='color:green'>Password for user <b>$username</b> has been reset successfully! (Using legacy MD5 hash, will be upgraded on next login)</p>";
            } else {
                echo "<p style='color:red'>User <b>$username</b> not found or password unchanged.</p>";
            }
        }

        echo '<form method="POST">
            Username: <input type="text" name="reset_user" required><br>
            New Password: <input type="password" name="new_password" required><br>
            <input type="submit" value="Reset Password">
        </form>';

    } catch (PDOException $e) {
        echo "<span style='color:red'>Database Connection Failed: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<span style='color:red'>NOT FOUND</span><br>";
    echo "Please check if the application is correctly installed on this server.";
}

echo "<h2>Environment Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Loaded Extensions: " . implode(', ', get_loaded_extensions()) . "<br>";

echo "<h2>Server Logs</h2>";
$logFile = __DIR__ . '/../src/log/XHRM.log';
if (file_exists($logFile)) {
    echo "Recent log entries (last 20 lines):<pre>";
    $logs = array_slice(file($logFile), -20);
    foreach ($logs as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "No XHRM log file found at <code>$logFile</code><br>";
}

echo "<hr><p style='color:red'><b>REMINDER: Delete this script immediately after use for security!</b></p>";
