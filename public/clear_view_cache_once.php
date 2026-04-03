<?php
/**
 * ONE-TIME: clear compiled Blade views (same as `php artisan view:clear`) without SSH/terminal.
 *
 * Use this when cPanel disables shell functions (`passthru` / `exec`) so `run_artisan.php` does not work.
 *
 * HOW TO USE
 * 1) Set $secret below to a long random string BEFORE uploading (or rely on ARTISAN_TOKEN in .env — see below).
 * 2) Upload to your **web public** folder (same folder as index.php), e.g. public_html/clear_view_cache_once.php
 * 3) Visit ONCE: https://your-domain.com/clear_view_cache_once.php?token=YOUR_SECRET
 * 4) Delete this file from the server immediately after success.
 *
 * TOKEN OPTIONS (first match wins)
 * - If .env contains ARTISAN_TOKEN=... that value is accepted (same as run_artisan.php).
 * - Otherwise the $secret in this file must be set (not the placeholder).
 *
 * SECURITY: Anyone with the URL + token can clear views. Use a strong token; delete this file after use.
 */

declare(strict_types=1);

$secret = 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_BEFORE_UPLOAD';

$basePath = null;
foreach (
    [
        dirname(__DIR__),                         // standard: this file in /public
        dirname(__DIR__) . '/veritytrade',       // common: public_html with app in sibling folder
        __DIR__,                                 // rare: artisan in same folder
    ] as $candidate
) {
    if (is_file($candidate . '/artisan') && is_file($candidate . '/bootstrap/app.php')) {
        $basePath = $candidate;
        break;
    }
}

$provided = isset($_GET['token']) ? (string) $_GET['token'] : '';

$expectedToken = null;
if ($basePath !== null) {
    $envPath = $basePath . DIRECTORY_SEPARATOR . '.env';
    if (is_file($envPath) && is_readable($envPath)) {
        $env = (string) file_get_contents($envPath);
        if (preg_match('/^\s*ARTISAN_TOKEN\s*=\s*(.+)/m', $env, $m)) {
            $expectedToken = trim($m[1], " \t\"'");
            if ($expectedToken === '') {
                $expectedToken = null;
            }
        }
    }
}

if ($expectedToken === null) {
    if ($secret === 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_BEFORE_UPLOAD' || $secret === '') {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Refusing to run: set \\\$secret in this file to a strong random value, or add ARTISAN_TOKEN to .env.\n";
        exit;
    }
    $expectedToken = $secret;
}

if ($basePath === null) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Cannot find Laravel (artisan + bootstrap/app.php). Upload this file next to index.php (inside public/), or edit paths in this script.\n";
    exit;
}

if (! hash_equals($expectedToken, $provided)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Forbidden';
    exit;
}

chdir($basePath);

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

try {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo \Illuminate\Support\Facades\Artisan::output();
    echo "view:clear completed OK.\n\n";
    echo "DELETE clear_view_cache_once.php from the server now.\n";
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
}
