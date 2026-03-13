<?php
/**
 * Run artisan commands via browser when no Terminal/SSH is available.
 * Visit: https://yoursite.com/run_artisan.php?token=veritytrade-setup-2024&cmd=cache:clear
 *
 * DELETE this file after use for security.
 */
$token = $_GET['token'] ?? '';
$cmd = $_GET['cmd'] ?? '';

if ($token !== 'veritytrade-setup-2024' || $cmd === '') {
    http_response_code(403);
    die('Forbidden. Use ?token=veritytrade-setup-2024&cmd=COMMAND');
}

// Allowed commands (whitelist – add more if needed)
$allowed = [
    'cache:clear',
    'config:cache',
    'config:clear',
    'route:cache',
    'route:clear',
    'view:cache',
    'view:clear',
    'optimize',
    'optimize:clear',
];

if (!in_array($cmd, $allowed, true)) {
    die('Command not allowed. Allowed: ' . implode(', ', $allowed));
}

$basePath = file_exists(dirname(__DIR__) . '/artisan') ? dirname(__DIR__) : dirname(__DIR__) . '/veritytrade';
if (!file_exists($basePath . '/artisan')) {
    die("Laravel not found at $basePath");
}

chdir($basePath);

header('Content-Type: text/plain; charset=utf-8');
echo "Running: php artisan $cmd\n\n";
passthru("php artisan $cmd 2>&1", $ret);
echo "\n\nExit code: $ret\n";
echo "Done. DELETE run_artisan.php for security.\n";
