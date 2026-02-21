<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test API Endpoint Directly</h2>";

// Test the API endpoint by making an internal curl request
$cookie = '';
$ch = curl_init();

// First, login
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://mimar.xsofty.com/web/index.php/auth/validate',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        '_username' => 'Admin',
        '_password' => 'Xsofty@1122',
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_COOKIEFILE => '',
    CURLOPT_COOKIEJAR => '/tmp/xhrm_cookies.txt',
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "<p>Login: HTTP $httpCode</p>";

// Get cookies
preg_match_all('/Set-Cookie:\s*([^;]*)/mi', $response, $matches);
$cookies = implode('; ', $matches[1]);

// Now test the salary components API
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://mimar.xsofty.com/web/index.php/api/v2/payroll/salary-components',
    CURLOPT_POST => false,
    CURLOPT_HTTPGET => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEFILE => '/tmp/xhrm_cookies.txt',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
    ],
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "<h3>GET /api/v2/payroll/salary-components</h3>";
echo "<p>HTTP $httpCode</p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test holidays
curl_setopt($ch, CURLOPT_URL, 'https://mimar.xsofty.com/web/index.php/api/v2/payroll/holidays');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "<h3>GET /api/v2/payroll/holidays</h3>";
echo "<p>HTTP $httpCode</p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test financial years
curl_setopt($ch, CURLOPT_URL, 'https://mimar.xsofty.com/web/index.php/api/v2/payroll/financial-years');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "<h3>GET /api/v2/payroll/financial-years</h3>";
echo "<p>HTTP $httpCode</p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

curl_close($ch);
