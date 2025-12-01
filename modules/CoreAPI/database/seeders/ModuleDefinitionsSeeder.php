<?php

namespace Database\Seeders;

use App\Models\ModuleDefinition;
use Illuminate\Database\Seeder;

class ModuleDefinitionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'module_key' => 'media',
                'module_name' => 'MediaService',
                'description' => 'Dịch vụ upload và lưu trữ file (ảnh, video)',
                'icon' => 'Image',
                'is_active' => true,
                'is_public' => true,
                'base_url' => 'https://media.cityresq360.io.vn',
                'docs_url' => '/documents/media-service',
                'default_max_storage_mb' => 1000,
                'default_max_requests_per_day' => 10000,
                'sort_order' => 1,
            ],
            [
                'module_key' => 'notification',
                'module_name' => 'NotificationService',
                'description' => 'Dịch vụ gửi thông báo (Push, Email, SMS)',
                'icon' => 'Bell',
                'is_active' => false, // Coming soon
                'is_public' => true,
                'base_url' => 'https://notification.cityresq360.io.vn',
                'docs_url' => '/documents/notification-service',
                'default_max_storage_mb' => 0,
                'default_max_requests_per_day' => 50000,
                'sort_order' => 2,
            ],
            [
                'module_key' => 'wallet',
                'module_name' => 'WalletService',
                'description' => 'Dịch vụ quản lý ví điện tử và giao dịch',
                'icon' => 'Wallet',
                'is_active' => false, // Coming soon
                'is_public' => true,
                'base_url' => 'https://wallet.cityresq360.io.vn',
                'docs_url' => '/documents/wallet-service',
                'default_max_storage_mb' => 0,
                'default_max_requests_per_day' => 100000,
                'sort_order' => 3,
            ],
            [
                'module_key' => 'iot',
                'module_name' => 'IoTService',
                'description' => 'Dịch vụ quản lý thiết bị IoT và telemetry',
                'icon' => 'Cpu',
                'is_active' => false, // Coming soon
                'is_public' => true,
                'base_url' => 'https://iot.cityresq360.io.vn',
                'docs_url' => '/documents/iot-service',
                'default_max_storage_mb' => 100,
                'default_max_requests_per_day' => 1000000,
                'sort_order' => 4,
            ],
            [
                'module_key' => 'incident',
                'module_name' => 'IncidentService',
                'description' => 'Dịch vụ quản lý sự cố và phân công xử lý',
                'icon' => 'AlertTriangle',
                'is_active' => false, // Coming soon
                'is_public' => true,
                'base_url' => 'https://incident.cityresq360.io.vn',
                'docs_url' => '/documents/incident-service',
                'default_max_storage_mb' => 500,
                'default_max_requests_per_day' => 50000,
                'sort_order' => 5,
            ],
            [
                'module_key' => 'analytics',
                'module_name' => 'AnalyticsService',
                'description' => 'Dịch vụ phân tích dữ liệu và báo cáo',
                'icon' => 'BarChart',
                'is_active' => false, // Coming soon
                'is_public' => true,
                'base_url' => 'https://analytics.cityresq360.io.vn',
                'docs_url' => '/documents/analytics-service',
                'default_max_storage_mb' => 0,
                'default_max_requests_per_day' => 10000,
                'sort_order' => 6,
            ],
            [
                'module_key' => 'search',
                'module_name' => 'SearchService',
                'description' => 'Dịch vụ tìm kiếm toàn văn (OpenSearch)',
                'icon' => 'Search',
                'is_active' => false, // Coming soon
                'is_public' => true,
                'base_url' => 'https://search.cityresq360.io.vn',
                'docs_url' => '/documents/search-service',
                'default_max_storage_mb' => 0,
                'default_max_requests_per_day' => 100000,
                'sort_order' => 7,
            ],
        ];

        foreach ($modules as $module) {
            ModuleDefinition::updateOrCreate(
                ['module_key' => $module['module_key']],
                $module
            );
        }

        $this->command->info('✅ Seeded 7 module definitions successfully!');
    }
}
