<?php
// Clear PHP OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!\n";
} else {
    echo "OPcache not available or already disabled.\n";
}

// Also clear any APC cache
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "APC cache cleared.\n";
}

echo "Done. Please test the app now.\n";
