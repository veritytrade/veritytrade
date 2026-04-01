<?php
/**
 * ONE-TIME Laravel migration runner (migrate only — no seed).
 *
 * HOW TO USE (production / cPanel, no SSH):
 * 1) Set $secret below to a long random string (e.g. openssl rand -hex 32) BEFORE uploading.
 * 2) Upload this file to your site’s **public** folder (same folder as index.php), e.g. public_html/run_migrate_once.php
 * 3) Visit ONCE: https://your-domain.com/run_migrate_once.php?token=YOUR_SECRET
 * 4) Read the output; if it says OK, **delete this file** from the server immediately.
 *
 * SECURITY: Anyone who guesses the URL + token can run migrations. Use a long secret; delete file after use.
 *
 * BEFORE YOU RUN — checklist:
 * - .env on the server has correct DB_* and APP_KEY (app must already boot).
 * - You have pulled/deployed the latest code (including new migration files).
 * - Optional: database backup via hosting panel.
 * - Optional: add INGESTION_API_KEY to .env for the scraper API (not required for migrate).
 */

declare(strict_types=1);

// MUST change this to a long random string before uploading to public/
$secret = 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_BEFORE_UPLOAD';

$provided = isset($_GET['token']) ? (string) $_GET['token'] : '';
if ($secret === 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_BEFORE_UPLOAD' || $secret === '') {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Refusing to run: set \\\$secret in this file to a strong random value first.\n";
    exit;
}

if (! hash_equals($secret, $provided)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Forbidden';
    exit;
}

// App root is one level above /public (adjust if your layout differs)
$basePath = dirname(__DIR__);
if (! is_file($basePath.'/bootstrap/app.php')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Cannot find Laravel at: {$basePath}\nEdit \\\$basePath in this script if your paths differ.\n";
    exit;
}

chdir($basePath);

require $basePath.'/vendor/autoload.php';
$app = require_once $basePath.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

try {
    @unlink($basePath.'/bootstrap/cache/config.php');
    echo "Config cache cleared (if present).\n\n";

    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();
    echo "\n\nDone. DELETE this PHP file from public/ now.\n";
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'ERROR: '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
}
