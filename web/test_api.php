<?php
// Intercept the actual API request by adding temporary logging
// This writes the actual request params to a file before validation

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>API Request Interceptor</h2>";

// Create a wrapper that logs the actual params before validation
$interceptorCode = <<<'PHP'
<?php
// TEMPORARY: Log all API request parameters before validation
// Added by diagnostic script - remove after debugging

namespace XHRM\Core\Api\V2\Validator;

class ValidatorInterceptor
{
    public static function logParams(array $values, ?ParamRuleCollection $rules): void
    {
        $logFile = realpath(__DIR__ . '/../../../../..') . '/web/api_debug_log.json';
        $entry = [
            'time' => date('Y-m-d H:i:s'),
            'params' => $values,
            'param_keys' => array_keys($values),
            'rules_keys' => $rules ? array_keys($rules->getMap()) : [],
            'is_strict' => $rules ? $rules->isStrict() : null,
            'unknown_keys' => $rules ? array_diff(array_keys($values), array_keys($rules->getMap())) : [],
        ];
        
        $existing = [];
        if (file_exists($logFile)) {
            $existing = json_decode(file_get_contents($logFile), true) ?: [];
        }
        $existing[] = $entry;
        // Keep only last 10 entries
        $existing = array_slice($existing, -10);
        file_put_contents($logFile, json_encode($existing, JSON_PRETTY_PRINT));
    }
}
PHP;

// Instead of injecting code, let's add logging directly via a simpler mechanism
// Modify the Validator.php to add file logging
$validatorFile = __DIR__ . '/../src/plugins/XHRMCorePlugin/Api/V2/Validator/Validator.php';
$validatorContent = file_get_contents($validatorFile);

// Check if we already added logging
if (strpos($validatorContent, 'api_debug_log') === false) {
    // Add logging at the start of the validate method
    $search = 'public static function validate(array $values, ?ParamRuleCollection $rules = null): bool
    {
        $paramRules = $rules->getMap();';

    $replace = 'public static function validate(array $values, ?ParamRuleCollection $rules = null): bool
    {
        // TEMPORARY DEBUG LOGGING - REMOVE AFTER DEBUGGING
        $debugLogFile = realpath(__DIR__ . \'/../../../../..\') . \'/web/api_debug_log.json\';
        $debugEntry = [
            \'time\' => date(\'Y-m-d H:i:s\'),
            \'request_uri\' => $_SERVER[\'REQUEST_URI\'] ?? \'unknown\',
            \'param_values\' => $values,
            \'rule_keys\' => $rules ? array_keys($rules->getMap()) : [],
            \'is_strict\' => $rules ? $rules->isStrict() : null,
            \'extra_params\' => $rules ? array_values(array_diff(array_keys($values), array_keys($rules->getMap()))) : [],
        ];
        $debugExisting = [];
        if (file_exists($debugLogFile)) {
            $debugExisting = json_decode(file_get_contents($debugLogFile), true) ?: [];
        }
        $debugExisting[] = $debugEntry;
        $debugExisting = array_slice($debugExisting, -20);
        @file_put_contents($debugLogFile, json_encode($debugExisting, JSON_PRETTY_PRINT));
        // END TEMPORARY DEBUG

        $paramRules = $rules->getMap();';

    if (strpos($validatorContent, $search) !== false) {
        $validatorContent = str_replace($search, $replace, $validatorContent);
        file_put_contents($validatorFile, $validatorContent);
        echo "<p style='color:green'>✓ Added debug logging to Validator.php</p>";
    } else {
        echo "<p style='color:red'>✗ Could not find injection point in Validator.php</p>";
        // Show the actual start of the validate method
        preg_match('/public static function validate.*?\{.*?\n.*?\n/s', $validatorContent, $m);
        echo "<pre>" . htmlspecialchars($m[0] ?? 'NOT FOUND') . "</pre>";
    }
} else {
    echo "<p style='color:blue'>Debug logging already present in Validator.php</p>";
}

// Clear the log file
file_put_contents(__DIR__ . '/api_debug_log.json', '[]');
echo "<p style='color:green'>✓ Cleared api_debug_log.json</p>";

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p style='color:green'>✓ OPcache cleared</p>";
}

echo "<p><b>Now visit any payroll page (e.g. Salary Components) and then check: <a href='api_debug_log.json'>api_debug_log.json</a></b></p>";
