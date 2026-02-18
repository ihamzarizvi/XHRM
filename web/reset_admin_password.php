<?php
/**
 * One-time script to reset the admin password using bcrypt (same as the app).
 * DELETE THIS FILE after use!
 */

// --- CONFIG ---
$newPassword = 'Pakistan#7890-';
$username = 'xsofty-admin'; // change if needed

// --- Load DB config ---
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/lib/confs/Conf.php';

$conf = new Conf();
$dsn = 'mysql:host=' . $conf->getDbHost() . ';dbname=' . $conf->getDbName() . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, $conf->getDbUser(), $conf->getDbPass(), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Generate bcrypt hash (same as app's PasswordHash::hash())
    $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

    $stmt = $pdo->prepare(
        "UPDATE ohrm_user SET user_password = ? WHERE user_name = ?"
    );
    $stmt->execute([$hash, $username]);

    $affected = $stmt->rowCount();

    echo "<h2>Password Reset</h2>";
    echo "<p>Rows affected: <strong>{$affected}</strong></p>";

    if ($affected === 1) {
        echo "<p style='color:green'>✅ Password for <strong>{$username}</strong> has been reset to: <code>{$newPassword}</code></p>";
        echo "<p style='color:red'><strong>DELETE this file from the server immediately!</strong></p>";
    } else {
        echo "<p style='color:red'>❌ No user found with username: <strong>{$username}</strong></p>";
        // Show all users
        $users = $pdo->query("SELECT id, user_name FROM ohrm_user")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Available users:</p><ul>";
        foreach ($users as $u) {
            echo "<li>{$u['id']}: {$u['user_name']}</li>";
        }
        echo "</ul>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
