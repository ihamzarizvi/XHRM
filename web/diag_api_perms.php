<?php
/**
 * Diagnostic: test the user-keys API endpoint directly.
 * DELETE THIS FILE after debugging!
 */

// Bootstrap the app
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/lib/confs/Conf.php';

$conf = new Conf();
$dsn = 'mysql:host=' . $conf->getDbHost() . ';dbname=' . $conf->getDbName() . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, $conf->getDbUser(), $conf->getDbPass(), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "<h2>API Permission Check</h2>";

    // 1. Check if the APIs are registered
    $stmt = $pdo->query("SELECT api_name, module_id, data_group_id FROM ohrm_api_permission WHERE api_name LIKE '%PasswordManager%' ORDER BY api_name");
    $apis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Registered APIs (" . count($apis) . ")</h3><table border='1' cellpadding='5'>";
    echo "<tr><th>api_name</th><th>module_id</th><th>data_group_id</th></tr>";
    foreach ($apis as $api) {
        $ok = $api['data_group_id'] ? '✅' : '❌ NULL';
        echo "<tr><td>{$api['api_name']}</td><td>{$api['module_id']}</td><td>{$ok} {$api['data_group_id']}</td></tr>";
    }
    echo "</table>";

    // 2. Check data group
    $stmt = $pdo->query("SELECT * FROM ohrm_data_group WHERE name = 'password_manager'");
    $dg = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<h3>Data Group</h3>";
    if ($dg) {
        echo "<pre>" . print_r($dg, true) . "</pre>";
    } else {
        echo "<p style='color:red'>❌ Data group 'password_manager' NOT FOUND</p>";
    }

    // 3. Check user role data group permissions
    $stmt = $pdo->query("
        SELECT urdg.*, dg.name AS dg_name, ur.name AS role_name
        FROM ohrm_user_role_data_group urdg
        JOIN ohrm_data_group dg ON urdg.data_group_id = dg.id
        JOIN ohrm_user_role ur ON urdg.user_role_id = ur.id
        WHERE dg.name = 'password_manager'
    ");
    $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Role Permissions (" . count($perms) . ")</h3><table border='1' cellpadding='5'>";
    echo "<tr><th>role</th><th>read</th><th>create</th><th>update</th><th>delete</th><th>self</th></tr>";
    foreach ($perms as $p) {
        echo "<tr><td>{$p['role_name']}</td><td>{$p['can_read']}</td><td>{$p['can_create']}</td><td>{$p['can_update']}</td><td>{$p['can_delete']}</td><td>{$p['self']}</td></tr>";
    }
    echo "</table>";

    // 4. Check module
    $stmt = $pdo->query("SELECT * FROM ohrm_module WHERE name LIKE '%assword%' OR name LIKE '%vault%'");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Module</h3><pre>" . print_r($modules, true) . "</pre>";

    // 5. Test the actual API via curl (internal)
    echo "<h3>Direct API Test</h3>";
    $cookies = [];
    foreach ($_COOKIE as $k => $v) {
        $cookies[] = "$k=" . urlencode($v);
    }
    $cookieStr = implode('; ', $cookies);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/web/index.php/api/v2/password-manager/user-keys');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Cookie: ' . $cookieStr]);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "<p>HTTP Status: <strong>$httpCode</strong></p>";
    echo "<pre>" . htmlspecialchars($result) . "</pre>";

} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
