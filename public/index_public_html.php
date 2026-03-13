<?php
/**
 * Use this file when document root MUST be public_html.
 * 1. Copy this file to public_html/index.php
 * 2. Copy .htaccess, build/, hot, mix-manifest.json to public_html
 * 3. Copy storage link or create it - see PUBLIC_HTML_SETUP.md
 */
define('LARAVEL_START', microtime(true));

$basePath = dirname(__DIR__).'/veritytrade';

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath.'/vendor/autoload.php';
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(\Illuminate\Http\Request::capture());
