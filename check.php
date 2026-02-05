<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XHRM Database Diagnostic</h1>";

try {
    require 'vendor/autoload.php';
    echo "<p style='color:green'>✅ Autoloader loaded.</p>";

    if (!class_exists('XHRM\Config\Conf')) {
        // The Conf.php file usually defines the 'Conf' class in global namespace or XHRM\Config
        // Based on your output, it's just 'class Conf' (global)
    }

    $autoloader = __DIR__ . '/lib/confs/Conf.php';
    if (file_exists($autoloader)) {
        require_once $autoloader;
        echo "<p style='color:green'>✅ Conf.php loaded.</p>";

        $conf = new Conf();
        $host = $conf->getDbHost();
        $port = $conf->getDbPort();
        $name = $conf->getDbName();
        $user = $conf->getDbUser();
        $pass = $conf->getDbPass();

        echo "<h3>Testing Connection to: $name</h3>";

        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            echo "<p style='color:green'>✅ DATABASE CONNECTED SUCCESSFULLY!</p>";

            // Check if tables exist
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($tables) > 0) {
                echo "<p style='color:blue'>💡 Found " . count($tables) . " tables. The database is NOT empty.</p>";
                echo "<ul>";
                foreach (array_slice($tables, 0, 5) as $table) {
                    echo "<li>$table</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color:orange'>⚠️ Connection successful, but the database is EMPTY.</p>";
            }
        } catch (\PDOException $e) {
            echo "<p style='color:red'>❌ CONNECTION FAILED: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Conf.php NOT found.</p>";
    }

} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>