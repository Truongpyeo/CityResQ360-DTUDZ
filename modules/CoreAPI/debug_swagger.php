<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

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
