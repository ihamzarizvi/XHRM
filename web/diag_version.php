<?php
/**
 * Diagnostic: Check which version of VaultAdminAPI is deployed
 * Visit: https://mimar.xsofty.com/web/diag_version.php
 * DELETE THIS FILE after debugging!
 */

$file = __DIR__ . '/../src/plugins/XHRMPasswordManagerPlugin/Api/VaultAdminAPI.php';

if (!file_exists($file)) {
    echo "ERROR: VaultAdminAPI.php not found at expected path.\n";
    exit;
}

$contents = file_get_contents($file);

echo "<pre>\n";
echo "=== VaultAdminAPI.php on server ===\n\n";

// Check for the problematic trait
if (strpos($contents, 'VaultPermissionTrait') !== false) {
    echo "❌ PROBLEM: VaultPermissionTrait is still present! Server has OLD code.\n";
    echo "   -> Git pull has NOT been done on Hostinger.\n";
} else {
    echo "✅ OK: VaultPermissionTrait is NOT present. Server has LATEST code.\n";
}

// Show first 30 lines
$lines = explode("\n", $contents);
echo "\n--- First 25 lines ---\n";
for ($i = 0; $i < min(25, count($lines)); $i++) {
    echo ($i + 1) . ": " . htmlspecialchars($lines[$i]) . "\n";
}

echo "\n=== Git info ===\n";
$gitDir = __DIR__ . '/../.git';
if (is_dir($gitDir)) {
    $headFile = $gitDir . '/HEAD';
    $head = file_get_contents($headFile);
    echo "HEAD: " . $head;

    // Get last commit hash
    $refPath = trim(str_replace('ref: ', '', $head));
    $refFile = $gitDir . '/' . $refPath;
    if (file_exists($refFile)) {
        echo "Last commit: " . file_get_contents($refFile);
    }
} else {
    echo "No .git directory found.\n";
}

echo "</pre>";
