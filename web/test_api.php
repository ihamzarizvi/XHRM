<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<h2>Direct API Validation Test</h2>";

// Bootstrap with composer autoloader
$autoloaderPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../src/vendor/autoload.php',
];
foreach ($autoloaderPaths as $alp) {
    if (file_exists($alp)) {
        require_once $alp;
        echo "<p style='color:green'>Loaded autoloader: $alp</p>";
        break;
    }
}

try {
    // Test 1: Can we instantiate the SalaryComponentAPI class?
    $apiClass = 'XHRM\\Payroll\\Api\\SalaryComponentAPI';
    echo "<p>Class exists: " . (class_exists($apiClass) ? 'YES' : 'NO') . "</p>";

    // Test 2: Check the validator directly
    $validatorClass = 'XHRM\\Core\\Api\\V2\\Validator\\Validator';
    $paramRuleClass = 'XHRM\\Core\\Api\\V2\\Validator\\ParamRuleCollection';
    $paramRule = 'XHRM\\Core\\Api\\V2\\Validator\\ParamRule';
    $ruleClass = 'XHRM\\Core\\Api\\V2\\Validator\\Rule';
    $rulesClass = 'XHRM\\Core\\Api\\V2\\Validator\\Rules';

    // Test 3: Simulate what the frontend sends 
    $simulatedParams = [
        'limit' => '50',
        'offset' => '0',
        'sortField' => 'salaryComponent.sortOrder',
        'sortOrder' => 'ASC',
    ];

    echo "<h3>Test: Validate simulated params</h3>";
    echo "<pre>Params: " . json_encode($simulatedParams, JSON_PRETTY_PRINT) . "</pre>";

    // Create validation rules matching SalaryComponentAPI::getValidationRuleForGetAll
    // But we need the Endpoint class methods... Let me try a simpler approach
    // Just test string validation

    $stringTypeClass = 'XHRM\\Core\\Api\\V2\\Validator\\Rules\\NotBlankStringType';
    if (class_exists($stringTypeClass)) {
        $rule = new $stringTypeClass();
        echo "<p>NotBlankStringType('ASC') = " . ($rule->validate('ASC') ? 'PASS' : 'FAIL') . "</p>";
        echo "<p>NotBlankStringType('50') = " . ($rule->validate('50') ? 'PASS' : 'FAIL') . "</p>";
    }

    // Test IN rule
    $inRule = new \Respect\Validation\Rules\In(['ASC', 'DESC']);
    echo "<p>IN(['ASC','DESC'], 'ASC') = " . ($inRule->validate('ASC') ? 'PASS' : 'FAIL') . "</p>";

    // Test Rules::IN with the sortField values
    $inRule2 = new \Respect\Validation\Rules\In(['salaryComponent.name', 'salaryComponent.sortOrder']);
    echo "<p>IN(sortFields, 'salaryComponent.sortOrder') = " . ($inRule2->validate('salaryComponent.sortOrder') ? 'PASS' : 'FAIL') . "</p>";

    // Test BOOL_VAL with null
    $boolVal = new \Respect\Validation\Rules\BoolVal();
    echo "<p>BoolVal(null) = " . ($boolVal->validate(null) ? 'PASS' : 'FAIL') . "</p>";
    echo "<p>BoolVal('true') = " . ($boolVal->validate('true') ? 'PASS' : 'FAIL') . "</p>";

    // Test ZeroOrPositive
    $zop = new XHRM\Core\Api\V2\Validator\Rules\ZeroOrPositive();
    echo "<p>ZeroOrPositive('50') = " . ($zop->validate('50') ? 'PASS' : 'FAIL') . "</p>";
    echo "<p>ZeroOrPositive('0') = " . ($zop->validate('0') ? 'PASS' : 'FAIL') . "</p>";

    // Now let's try full validation
    echo "<h3>Full Validator Test</h3>";

    // Build rules similar to getSortingAndPaginationParamsRules
    $rules = new $paramRuleClass(
        // sortOrder
        new $paramRule('sortOrder', new $ruleClass(\Respect\Validation\Rules\In::class, [['ASC', 'DESC']])),
        // limit
        new $paramRule('limit', new $ruleClass(XHRM\Core\Api\V2\Validator\Rules\ZeroOrPositive::class)),
        // offset
        new $paramRule('offset', new $ruleClass(XHRM\Core\Api\V2\Validator\Rules\ZeroOrPositive::class)),
        // sortField
        new $paramRule('sortField', new $ruleClass(\Respect\Validation\Rules\In::class, [['salaryComponent.name', 'salaryComponent.sortOrder']])),
    );
    $rules->setStrict(false);

    try {
        $result = $validatorClass::validate($simulatedParams, $rules);
        echo "<p style='color:green'>✓ Validation PASSED!</p>";
    } catch (\XHRM\Core\Api\V2\Exception\InvalidParamException $e) {
        echo "<p style='color:red'>✗ Validation FAILED: " . $e->getMessage() . "</p>";
        $bag = $e->getNormalizedErrorBag();
        echo "<pre>" . json_encode($bag, JSON_PRETTY_PRINT) . "</pre>";
    }

} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
