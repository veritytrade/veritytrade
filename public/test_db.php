<?php
/**
 * Database connection test. DELETE after fixing.
 * Visit: https://veritytrade.ng/test_db.php?token=veritytrade-setup-2024
 */
$token = $_GET['token'] ?? '';
if ($token !== 'veritytrade-setup-2024') {
    die('Forbidden');
}

$basePath = '/home/veritytr/veritytrade';
$envFile = $basePath.'/.env';

if (!file_exists($envFile)) {
    die('.env not found');
}

$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if ($line && strpos($line, '#') !== 0 && strpos($line, '=') !== false) {
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
    }
}
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? '';
$username = $env['DB_USERNAME'] ?? '';
$password = $env['DB_PASSWORD'] ?? '';

header('Content-Type: text/plain; charset=utf-8');
echo "DB Connection Test\n";
echo "==================\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? '(empty)' : '(set)') . "\n\n";

$dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "SUCCESS: Connected to database.\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$database'");
    $count = $stmt->fetchColumn();
    echo "Tables in database: $count\n";
    if ($count === 0) {
        echo "\nDatabase is empty. Run migrations: visit setup.php?token=veritytrade-setup-2024\n";
    }
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n\n";
    echo "Common fixes:\n";
    echo "1. cPanel → MySQL Databases → Add User To Database → veritytr_gadgets + veritytr_veritytrade → All Privileges\n";
    echo "2. Verify .env has exact names from cPanel (including veritytr_ prefix)\n";
    echo "3. Try DB_HOST=localhost instead of 127.0.0.1 (or vice versa)\n";
    echo "4. Check password is correct\n";
}
