<?php
/**
 * Run artisan commands via browser when no Terminal/SSH is available.
 * Visit: https://yoursite.com/run_artisan.php?token=YOUR_TOKEN&cmd=cache:clear
 *
 * Token: set ARTISAN_TOKEN in .env (recommended), or falls back to veritytrade-setup-2024.
 * DELETE this file when you no longer need it (e.g. once you have SSH).
 */
$basePath = file_exists(dirname(__DIR__) . '/artisan') ? dirname(__DIR__) : dirname(__DIR__) . '/veritytrade';
$envPath = $basePath . DIRECTORY_SEPARATOR . '.env';
$expectedToken = 'veritytrade-setup-2024';
if (is_file($envPath) && is_readable($envPath)) {
    $env = file_get_contents($envPath);
    if (preg_match('/^\s*ARTISAN_TOKEN\s*=\s*(.+)/m', $env, $m)) {
        $expectedToken = trim($m[1], " \t\"'");
    }
}

$token = $_GET['token'] ?? '';
$cmd = $_GET['cmd'] ?? '';

if ($token === '' || $cmd === '' || !hash_equals($expectedToken, $token)) {
    http_response_code(403);
    die('Forbidden. Use ?token=YOUR_TOKEN&cmd=COMMAND');
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

if (!file_exists($basePath . '/artisan')) {
    die("Laravel not found at $basePath");
}

chdir($basePath);

header('Content-Type: text/plain; charset=utf-8');
echo "Running: php artisan $cmd\n\n";
passthru("php artisan $cmd 2>&1", $ret);
echo "\n\nExit code: $ret\n";
echo "Done. DELETE run_artisan.php for security.\n";
