<?php
/**
 * Check git status and pull latest code
 */
$base = '/home/u118669189/domains/xsofty.com/public_html/mimarhrm';

echo "<pre>\n";

// Check current git status
$gitLog = shell_exec("cd $base && git log --oneline -5 2>&1");
echo "=== Last 5 commits ===\n$gitLog\n";

// Check git status
$gitStatus = shell_exec("cd $base && git status 2>&1");
echo "=== Git Status ===\n$gitStatus\n";

// Pull latest
$gitPull = shell_exec("cd $base && git pull origin main 2>&1");
echo "=== Git Pull ===\n$gitPull\n";

// Check again
$gitLog2 = shell_exec("cd $base && git log --oneline -3 2>&1");
echo "=== After Pull - Last 3 commits ===\n$gitLog2\n";

echo "</pre>";
