<?php
/**
 * Run migrations when database is empty. DELETE after it succeeds.
 * Visit: https://veritytrade.ng/run_migrations.php?token=veritytrade-setup-2024
 */
$token = $_GET['token'] ?? '';
if ($token !== 'veritytrade-setup-2024') {
    die('Forbidden');
}

$basePath = '/home/veritytr/veritytrade';
chdir($basePath);

// Load Laravel
require $basePath.'/vendor/autoload.php';
$app = require_once $basePath.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

@unlink($basePath.'/bootstrap/cache/config.php');
if (empty(config('app.key'))) {
    \Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
    echo "Generated APP_KEY.\n\n";
}
echo "Cleared config cache.\n\n";
echo "Running migrations...\n\n";

try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();
    echo "\nMigrations OK. Running seed...\n\n";
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();
    echo "\nSeeding OK. Caching config...\n\n";
    \Illuminate\Support\Facades\Artisan::call('config:cache');
    \Illuminate\Support\Facades\Artisan::call('route:cache');
    \Illuminate\Support\Facades\Artisan::call('view:cache');
    echo "\nDone! Try the homepage now. DELETE this file.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
}
