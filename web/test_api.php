<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>API Debug - Check what params fail validation</h2>";

// Load autoloader
require_once __DIR__ . '/../src/vendor/autoload.php';
$confPaths = [__DIR__ . '/../lib/confs/Conf.php', __DIR__ . '/../src/lib/confs/Conf.php'];
foreach ($confPaths as $cp) {
    if (file_exists($cp)) {
        require_once $cp;
        break;
    }
}
$conf = new Conf();
$pdo = new PDO("mysql:host={$conf->getDbHost()};dbname={$conf->getDbName()};port={$conf->getDbPort()}", $conf->getDbUser(), $conf->getDbPass(), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Retrieve the validation rules from the SalaryComponentAPI
echo "<h3>1. Checking SalaryComponent getAll Validation Rules</h3>";
try {
    // Check what the actual validation rule names are
    $ruleClass = 'XHRM\\Core\\Api\\V2\\Validator\\ParamRuleCollection';
    $endpointClass = 'XHRM\\Payroll\\Api\\SalaryComponentAPI';

    echo "<p>SalaryComponentAPI class exists: " . (class_exists($endpointClass) ? 'YES' : 'NO') . "</p>";
    echo "<p>ParamRuleCollection class exists: " . (class_exists($ruleClass) ? 'YES' : 'NO') . "</p>";

    // Check the actual sort/pagination param names
    $commonParamsClass = 'XHRM\\Core\\Api\\CommonParams';
    if (class_exists($commonParamsClass)) {
        $reflector = new ReflectionClass($commonParamsClass);
        $constants = $reflector->getConstants();
        echo "<p><b>CommonParams constants:</b></p><pre>";
        foreach ($constants as $k => $v) {
            if (stripos($k, 'SORT') !== false || stripos($k, 'LIMIT') !== false || stripos($k, 'OFFSET') !== false || stripos($k, 'PAGE') !== false) {
                echo "$k = $v\n";
            }
        }
        echo "</pre>";
    }

    // Check the Request class getAllParameters method
    $reqClass = 'XHRM\\Core\\Api\\V2\\Request';
    if (class_exists($reqClass)) {
        $reflector = new ReflectionClass($reqClass);
        $method = $reflector->getMethod('getAllParameters');
        echo "<p>Request::getAllParameters() method exists on line " . $method->getStartLine() . "-" . $method->getEndLine() . "</p>";
        echo "<p>File: " . $method->getFileName() . "</p>";

        // Read the actual source code of the method
        $source = file_get_contents($method->getFileName());
        $lines = explode("\n", $source);
        $methodLines = array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);
        echo "<pre>";
        foreach ($methodLines as $i => $line) {
            echo ($method->getStartLine() + $i) . ": " . htmlspecialchars($line) . "\n";
        }
        echo "</pre>";
    }

} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 2. Simulate what the frontend sends and check
echo "<h3>2. Simulated Request Params</h3>";
$simulatedParams = [
    'limit' => '50',
    'offset' => '0',
    'sortField' => 'salaryComponent.sortOrder',
    'sortOrder' => 'ASC',
];
echo "<p>Frontend GET sends: </p><pre>" . json_encode($simulatedParams, JSON_PRETTY_PRINT) . "</pre>";

// 3. Check if the entity mapping is correct
echo "<h3>3. Entity Check</h3>";
try {
    $entityClass = 'XHRM\\Entity\\SalaryComponent';
    if (class_exists($entityClass)) {
        $ref = new ReflectionClass($entityClass);
        echo "<p style='color:green'>✓ SalaryComponent entity at: " . $ref->getFileName() . "</p>";
        echo "<p>Doc comment: " . htmlspecialchars($ref->getDocComment()) . "</p>";
    }

    $entityClass2 = 'XHRM\\Entity\\Holiday';
    if (class_exists($entityClass2)) {
        $ref2 = new ReflectionClass($entityClass2);
        echo "<p style='color:green'>✓ Holiday entity at: " . $ref2->getFileName() . "</p>";
        echo "<p>Doc comment: " . htmlspecialchars($ref2->getDocComment()) . "</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// 4. Check if Doctrine's metadata cache has entries for payroll entities
echo "<h3>4. Doctrine Metadata Cache</h3>";
$metaCacheDir = __DIR__ . '/../src/cache/doctrine_metadata';
if (is_dir($metaCacheDir)) {
    $files = [];
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($metaCacheDir));
    foreach ($iter as $f) {
        if ($f->isFile())
            $files[] = $f->getFilename();
    }
    echo "<p>Total cached metadata files: " . count($files) . "</p>";
    // Look for payroll-related
    $payrollFiles = array_filter($files, fn($f) => stripos($f, 'SalaryComponent') !== false || stripos($f, 'Holiday') !== false || stripos($f, 'FinancialYear') !== false);
    echo "<p>Payroll metadata files: " . count($payrollFiles) . "</p>";
    foreach ($payrollFiles as $pf)
        echo "<p>  - $pf</p>";
}
