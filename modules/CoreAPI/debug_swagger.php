<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$documentation = 'default';
$asset = 'swagger-ui.css';

$configPath = config('l5-swagger.documentations.' . $documentation . '.paths.swagger_ui_assets_path');
echo "Config Path: " . $configPath . "\n";

$basePath = base_path($configPath);
echo "Base Path + Config: " . $basePath . "\n";

$fullPath = $basePath . $asset;
echo "Full Path: " . $fullPath . "\n";

if (file_exists($fullPath)) {
    echo "File EXISTS\n";
} else {
    echo "File DOES NOT EXIST\n";
}

// Check symlink
if (is_link($basePath)) {
    echo "Is Link: YES\n";
    echo "Link Target: " . readlink($basePath) . "\n";
} else {
    echo "Is Link: NO\n";
}

// Check realpath
echo "Realpath: " . realpath($fullPath) . "\n";

echo "\n--- Config Check ---\n";
$docsConfig = config('l5-swagger.documentations');
print_r(array_keys($docsConfig));

$defaultConfig = config('l5-swagger.documentations.default');
if (isset($defaultConfig['routes']['docs'])) {
    echo "Routes Docs: " . $defaultConfig['routes']['docs'] . "\n";
} else {
    echo "Routes Docs: NOT SET\n";
}
