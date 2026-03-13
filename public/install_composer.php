<?php
/**
 * ONE-TIME: Install Composer deps when vendor/ is missing.
 * Visit: https://veritytrade.ng/install_composer.php?token=veritytrade-setup-2024
 * DELETE after it succeeds.
 */
$token = $_GET['token'] ?? '';
if ($token !== 'veritytrade-setup-2024') {
    die('Forbidden');
}

$basePath = '/home/veritytr/veritytrade';
chdir($basePath);

header('Content-Type: text/plain; charset=utf-8');
echo "Installing Composer dependencies...\n\n";

$paths = ['composer', '/usr/local/bin/composer', '/usr/bin/composer'];
$composer = null;
foreach ($paths as $p) {
    $out = [];
    exec("$p --version 2>&1", $out, $ret);
    if ($ret === 0) {
        $composer = $p;
        break;
    }
}

if (!$composer) {
    echo "Composer not found on server. You must upload vendor/ manually:\n";
    echo "1. On your PC: cd veritytrade, run 'composer install --no-dev'\n";
    echo "2. Zip the vendor/ folder, upload to veritytrade/, extract.\n";
    exit(1);
}

echo "Using: $composer\n\n";
passthru("$composer install --no-dev --optimize-autoloader 2>&1", $ret);
echo "\nExit code: $ret\n";

if ($ret === 0) {
    echo "\nDone! Now visit setup.php to complete, then DELETE both files.\n";
} else {
    echo "\nFailed. Try uploading vendor/ from your PC (see instructions above).\n";
}
