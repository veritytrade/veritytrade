<?php
/**
 * Run this once to see the real 500 error. DELETE after fixing.
 * Visit: https://veritytrade.ng/debug_500.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$basePath = dirname(__DIR__).'/veritytrade';

echo "<pre>\n";
echo "Base path: $basePath\n";
echo "Exists: " . (file_exists($basePath) ? 'yes' : 'no') . "\n";
echo "Vendor: " . (file_exists($basePath.'/vendor/autoload.php') ? 'yes' : 'no') . "\n";
echo ".env: " . (file_exists($basePath.'/.env') ? 'yes' : 'no') . "\n";
echo "Bootstrap: " . (file_exists($basePath.'/bootstrap/app.php') ? 'yes' : 'no') . "\n\n";

try {
    require $basePath.'/vendor/autoload.php';
    $app = require_once $basePath.'/bootstrap/app.php';
    $app->handleRequest(\Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n" . $e->getTraceAsString();
}
