<?php
/**
 * ONE-TIME: apply the "Flying to Nigeria" → "In Transit to Nigeria" DB update via Laravel migrate,
 * without SSH (same idea as clear_view_cache_once.php — works from public_html when .htaccess blocks other routes).
 *
 * HOW TO USE
 * 1) Set $secret below to a long random string, OR use ARTISAN_TOKEN in .env (same as clear_view_cache_once.php).
 * 2) Upload this file to your **web public** folder (same folder as index.php), e.g. public_html/run_in_transit_stage_migration_once.php
 * 3) Visit ONCE: https://your-domain.com/run_in_transit_stage_migration_once.php?token=YOUR_SECRET
 * 4) Delete this file from the server immediately after success.
 *
 * SECURITY: Anyone with URL + token can run migrations. Use a strong token; delete this file after use.
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

$migrationRelative = 'database/migrations/2026_04_03_120000_rename_flying_to_in_transit_to_nigeria_stage.php';
$migrationPath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $migrationRelative);

if (! is_file($migrationPath)) {
    http_response_code(500);
    echo "Migration file not found: {$migrationRelative}\n";
    echo 'Deploy the latest code, or run this migration from the server shell instead.' . "\n";
    exit;
}

try {
    \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => $migrationRelative,
        '--force' => true,
    ]);
    echo \Illuminate\Support\Facades\Artisan::output();
    echo "Migration completed (or already applied). Check messages above.\n\n";
    echo "DELETE run_in_transit_stage_migration_once.php from the server now.\n";
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
}
