<?php
/**
 * ONE-TIME SETUP – Run once via browser, then DELETE this file.
 * Use when you have no Terminal: generates key, storage link, migrate, seed, cache.
 *
 * Security: Protect with ?token=YOUR_SECRET or run from localhost only.
 * DELETE THIS FILE after use.
 */
$secret = $_GET['token'] ?? '';
if (empty($secret) || $secret !== 'veritytrade-setup-2024') {
    http_response_code(403);
    die('Forbidden');
}

// Support both: veritytrade/public (normal) and public_html (when doc root can't change)
$basePath = file_exists(dirname(__DIR__).'/artisan') ? dirname(__DIR__) : dirname(__DIR__).'/veritytrade';
if (!file_exists($basePath.'/artisan')) {
    die("Laravel not found at $basePath");
}
chdir($basePath);

$commands = [
    'php artisan key:generate --force',
    'php artisan storage:link --force',
    'php artisan migrate --force',
    'php artisan db:seed --force',
    'php artisan config:cache',
    'php artisan route:cache',
    'php artisan view:cache',
];

header('Content-Type: text/plain; charset=utf-8');
echo "VerityTrade Setup\n";
echo "================\n\n";

foreach ($commands as $cmd) {
    echo "Running: $cmd\n";
    passthru($cmd . ' 2>&1', $ret);
    echo "\n";
    if ($ret !== 0) {
        echo "WARNING: Command exited with code $ret\n\n";
    }
}

echo "Done. DELETE this file (public/setup.php) for security.\n";
