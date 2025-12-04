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

use App\Models\CauHinhHeThong;
use Illuminate\Database\Seeder;

class CauHinhHeThongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            // General Settings
            [
                'khoa_cau_hinh' => 'app.name',
                'gia_tri' => 'CityResQ360',
                'loai_du_lieu' => CauHinhHeThong::LOAI_STRING,
                'mo_ta' => 'Tên ứng dụng',
                'nhom' => CauHinhHeThong::NHOM_GENERAL,
            ],
            [
                'khoa_cau_hinh' => 'app.version',
                'gia_tri' => '1.0.0',
                'loai_du_lieu' => CauHinhHeThong::LOAI_STRING,
                'mo_ta' => 'Phiên bản ứng dụng',
                'nhom' => CauHinhHeThong::NHOM_GENERAL,
            ],
            [
                'khoa_cau_hinh' => 'app.maintenance_mode',
                'gia_tri' => 'false',
                'loai_du_lieu' => CauHinhHeThong::LOAI_BOOLEAN,
                'mo_ta' => 'Chế độ bảo trì',
                'nhom' => CauHinhHeThong::NHOM_GENERAL,
            ],

            // Report Settings
            [
                'khoa_cau_hinh' => 'report.auto_assign',
                'gia_tri' => 'true',
                'loai_du_lieu' => CauHinhHeThong::LOAI_BOOLEAN,
                'mo_ta' => 'Tự động phân công phản ánh cho cơ quan',
                'nhom' => CauHinhHeThong::NHOM_REPORT,
            ],
            [
                'khoa_cau_hinh' => 'report.max_images',
                'gia_tri' => '5',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Số ảnh tối đa cho mỗi phản ánh',
                'nhom' => CauHinhHeThong::NHOM_REPORT,
            ],
            [
                'khoa_cau_hinh' => 'report.response_deadline_hours',
                'gia_tri' => '72',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Thời hạn phản hồi phản ánh (giờ)',
                'nhom' => CauHinhHeThong::NHOM_REPORT,
            ],
            [
                'khoa_cau_hinh' => 'report.min_trust_score',
                'gia_tri' => '0.5',
                'loai_du_lieu' => CauHinhHeThong::LOAI_FLOAT,
                'mo_ta' => 'Điểm tin cậy tối thiểu để hiển thị công khai',
                'nhom' => CauHinhHeThong::NHOM_REPORT,
            ],

            // Gamification Settings
            [
                'khoa_cau_hinh' => 'gamification.points_per_report',
                'gia_tri' => '50',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Số điểm thành phố nhận được khi tạo phản ánh',
                'nhom' => CauHinhHeThong::NHOM_GAMIFICATION,
            ],
            [
                'khoa_cau_hinh' => 'gamification.points_per_accurate_report',
                'gia_tri' => '100',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Điểm thưởng cho phản ánh chính xác',
                'nhom' => CauHinhHeThong::NHOM_GAMIFICATION,
            ],
            [
                'khoa_cau_hinh' => 'gamification.bronze_threshold',
                'gia_tri' => '0',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Điểm cần thiết để đạt huy hiệu Đồng',
                'nhom' => CauHinhHeThong::NHOM_GAMIFICATION,
            ],
            [
                'khoa_cau_hinh' => 'gamification.silver_threshold',
                'gia_tri' => '500',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Điểm cần thiết để đạt huy hiệu Bạc',
                'nhom' => CauHinhHeThong::NHOM_GAMIFICATION,
            ],
            [
                'khoa_cau_hinh' => 'gamification.gold_threshold',
                'gia_tri' => '1000',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Điểm cần thiết để đạt huy hiệu Vàng',
                'nhom' => CauHinhHeThong::NHOM_GAMIFICATION,
            ],
            [
                'khoa_cau_hinh' => 'gamification.platinum_threshold',
                'gia_tri' => '2000',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Điểm cần thiết để đạt huy hiệu Bạch Kim',
                'nhom' => CauHinhHeThong::NHOM_GAMIFICATION,
            ],

            // Notification Settings
            [
                'khoa_cau_hinh' => 'notification.email_enabled',
                'gia_tri' => 'true',
                'loai_du_lieu' => CauHinhHeThong::LOAI_BOOLEAN,
                'mo_ta' => 'Bật thông báo email',
                'nhom' => CauHinhHeThong::NHOM_NOTIFICATION,
            ],
            [
                'khoa_cau_hinh' => 'notification.push_enabled',
                'gia_tri' => 'true',
                'loai_du_lieu' => CauHinhHeThong::LOAI_BOOLEAN,
                'mo_ta' => 'Bật thông báo push',
                'nhom' => CauHinhHeThong::NHOM_NOTIFICATION,
            ],
            [
                'khoa_cau_hinh' => 'notification.sms_enabled',
                'gia_tri' => 'false',
                'loai_du_lieu' => CauHinhHeThong::LOAI_BOOLEAN,
                'mo_ta' => 'Bật thông báo SMS',
                'nhom' => CauHinhHeThong::NHOM_NOTIFICATION,
            ],

            // AI Settings
            [
                'khoa_cau_hinh' => 'ai.auto_classify',
                'gia_tri' => 'true',
                'loai_du_lieu' => CauHinhHeThong::LOAI_BOOLEAN,
                'mo_ta' => 'Tự động phân loại phản ánh bằng AI',
                'nhom' => CauHinhHeThong::NHOM_AI,
            ],
            [
                'khoa_cau_hinh' => 'ai.min_confidence',
                'gia_tri' => '0.7',
                'loai_du_lieu' => CauHinhHeThong::LOAI_FLOAT,
                'mo_ta' => 'Độ tin cậy tối thiểu của AI classification',
                'nhom' => CauHinhHeThong::NHOM_AI,
            ],
            [
                'khoa_cau_hinh' => 'ai.image_analysis_enabled',
                'gia_tri' => 'true',
                'loai_du_lieu' => CauHinhHeThong::LOAI_BOOLEAN,
                'mo_ta' => 'Bật phân tích ảnh bằng AI',
                'nhom' => CauHinhHeThong::NHOM_AI,
            ],

            // Flood Settings
            [
                'khoa_cau_hinh' => 'flood.alert_threshold_cm',
                'gia_tri' => '30',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Ngưỡng cảnh báo ngập lụt (cm)',
                'nhom' => CauHinhHeThong::NHOM_FLOOD,
            ],
            [
                'khoa_cau_hinh' => 'flood.danger_threshold_cm',
                'gia_tri' => '50',
                'loai_du_lieu' => CauHinhHeThong::LOAI_INTEGER,
                'mo_ta' => 'Ngưỡng nguy hiểm ngập lụt (cm)',
                'nhom' => CauHinhHeThong::NHOM_FLOOD,
            ],
            [
                'khoa_cau_hinh' => 'flood.prediction_enabled',
                'gia_tri' => 'true',
                'loai_du_lieu' => CauHinhHeThong::LOAI_BOOLEAN,
                'mo_ta' => 'Bật dự đoán ngập lụt',
                'nhom' => CauHinhHeThong::NHOM_FLOOD,
            ],
        ];

        foreach ($configs as $config) {
            CauHinhHeThong::firstOrCreate(
                ['khoa_cau_hinh' => $config['khoa_cau_hinh']],
                $config
            );
        }

        $this->command->info('✅ Created '.count($configs).' system configurations');
        $this->command->info('   - General: 3 configs');
        $this->command->info('   - Report: 4 configs');
        $this->command->info('   - Gamification: 6 configs');
        $this->command->info('   - Notification: 3 configs');
        $this->command->info('   - AI: 3 configs');
        $this->command->info('   - Flood: 3 configs');
    }
}
