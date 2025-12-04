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

use App\Models\HinhAnhPhanAnh;
use App\Models\PhanAnh;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HinhAnhPhanAnh::query()->delete();

        // Add media for Report 12 (User 1)
        $report = PhanAnh::find(12);
        if ($report) {
            HinhAnhPhanAnh::create([
                'id' => 1, // Explicit ID for testing
                'phan_anh_id' => 12,
                'nguoi_dung_id' => 1,
                'duong_dan_hinh_anh' => 'https://example.com/image1.jpg',
                'duong_dan_thumbnail' => 'https://example.com/thumb1.jpg',
                'loai_file' => 'image',
                'kich_thuoc' => 1024,
            ]);
            
            HinhAnhPhanAnh::create([
                'id' => 2,
                'phan_anh_id' => 12,
                'nguoi_dung_id' => 1,
                'duong_dan_hinh_anh' => 'https://example.com/image2.jpg',
                'duong_dan_thumbnail' => 'https://example.com/thumb2.jpg',
                'loai_file' => 'image',
                'kich_thuoc' => 2048,
            ]);
        }

        $this->command->info('✅ Created media for Report 12');
    }
}
