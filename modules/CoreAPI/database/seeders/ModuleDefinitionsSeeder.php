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
                'is_active' => true, // Activated
                'is_public' => true,
                'base_url' => 'https://notification.cityresq360.io.vn',
                'docs_url' => '/documents/notification-service',
                'default_max_storage_mb' => 0,
                'default_max_requests_per_day' => 50000,
                'sort_order' => 2,
            ],
            [
                'module_key' => 'search',
                'module_name' => 'SearchService',
                'description' => 'Dịch vụ tìm kiếm reports/incidents đô thị',
                'icon' => 'Search',
                'is_active' => true, // Activated
                'is_public' => true,
                'base_url' => 'https://search.cityresq360.io.vn',
                'docs_url' => '/documents/search-service',
                'default_max_storage_mb' => 0,
                'default_max_requests_per_day' => 100000,
                'sort_order' => 3,
            ],
            [
                'module_key' => 'iot',
                'module_name' => 'IoTService',
                'description' => 'Dịch vụ thu thập dữ liệu IoT đô thị',
                'icon' => 'Cpu',
                'is_active' => true, // Activated
                'is_public' => true,
                'base_url' => 'https://iot.cityresq360.io.vn',
                'docs_url' => '/documents/iot-service',
                'default_max_storage_mb' => 100,
                'default_max_requests_per_day' => 1000000,
                'sort_order' => 4,
            ],
            [
                'module_key' => 'analytics',
                'module_name' => 'AnalyticsService',
                'description' => 'Dịch vụ phân tích dữ liệu civic engagement',
                'icon' => 'BarChart',
                'is_active' => true, // Activated
                'is_public' => true,
                'base_url' => 'https://analytics.cityresq360.io.vn',
                'docs_url' => '/documents/analytics-service',
                'default_max_storage_mb' => 0,
                'default_max_requests_per_day' => 10000,
                'sort_order' => 5,
            ],
            [
                'module_key' => 'floodeye',
                'module_name' => 'FloodEyeService',
                'description' => 'Dịch vụ dự báo và cảnh báo ngập lụt đô thị',
                'icon' => 'Droplets',
                'is_active' => true,
                'is_public' => true,
                'base_url' => 'https://floodeye.cityresq360.io.vn',
                'docs_url' => '/documents/floodeye-service',
                'default_max_storage_mb' => 100,
                'default_max_requests_per_day' => 10000,
                'sort_order' => 6,
            ],
            [
                'module_key' => 'incident',
                'module_name' => 'IncidentService',
                'description' => 'Dịch vụ quản lý và điều phối sự cố đô thị',
                'icon' => 'AlertTriangle',
                'is_active' => true, // Activated
                'is_public' => true,
                'base_url' => 'https://incident.cityresq360.io.vn',
                'docs_url' => '/documents/incident-service',
                'default_max_storage_mb' => 500,
                'default_max_requests_per_day' => 50000,
                'sort_order' => 7,
            ],
            [
                'module_key' => 'aiml',
                'module_name' => 'AIMLService',
                'description' => 'Dịch vụ AI/ML phân loại sự cố đô thị',
                'icon' => 'Brain',
                'is_active' => true,
                'is_public' => true,
                'base_url' => 'https://aiml.cityresq360.io.vn',
                'docs_url' => '/documents/aiml-service',
                'default_max_storage_mb' => 50,
                'default_max_requests_per_day' => 50000,
                'sort_order' => 8,
            ],
        ];

        foreach ($modules as $module) {
            ModuleDefinition::updateOrCreate(
                ['module_key' => $module['module_key']],
                $module
            );
        }

        $this->command->info('✅ Seeded 8 module definitions successfully!');
    }
}
