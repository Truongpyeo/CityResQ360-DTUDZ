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

use App\Models\QuanTriVien;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QuanTriVienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'ten_quan_tri' => 'Super Admin',
                'email' => 'superadmin@cityresq.com',
                'mat_khau' => Hash::make('password123'), // password: password123
                'vai_tro' => QuanTriVien::VAI_TRO_SUPERADMIN,
                'anh_dai_dien' => null,
                'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
                'lan_dang_nhap_cuoi' => now(),
            ],
            [
                'ten_quan_tri' => 'Data Admin',
                'email' => 'dataadmin@cityresq.com',
                'mat_khau' => Hash::make('password123'), // password: password123
                'vai_tro' => QuanTriVien::VAI_TRO_DATA_ADMIN,
                'anh_dai_dien' => null,
                'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
                'lan_dang_nhap_cuoi' => null,
            ],
            [
                'ten_quan_tri' => 'Agency Admin District 1',
                'email' => 'agency1@cityresq.com',
                'mat_khau' => Hash::make('password123'), // password: password123
                'vai_tro' => QuanTriVien::VAI_TRO_AGENCY_ADMIN,
                'anh_dai_dien' => null,
                'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
                'lan_dang_nhap_cuoi' => null,
            ],
            [
                'ten_quan_tri' => 'Agency Admin District 3',
                'email' => 'agency3@cityresq.com',
                'mat_khau' => Hash::make('password123'), // password: password123
                'vai_tro' => QuanTriVien::VAI_TRO_AGENCY_ADMIN,
                'anh_dai_dien' => null,
                'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
                'lan_dang_nhap_cuoi' => null,
            ],
            [
                'ten_quan_tri' => 'Test Admin (Locked)',
                'email' => 'locked@cityresq.com',
                'mat_khau' => Hash::make('password123'), // password: password123
                'vai_tro' => QuanTriVien::VAI_TRO_DATA_ADMIN,
                'anh_dai_dien' => null,
                'trang_thai' => QuanTriVien::TRANG_THAI_LOCKED,
                'lan_dang_nhap_cuoi' => null,
            ],
        ];

        foreach ($admins as $admin) {
            QuanTriVien::create($admin);
        }

        $this->command->info('✅ Created ' . count($admins) . ' admin users');
        $this->command->info('📧 Email: superadmin@cityresq.com | Password: password123');
        $this->command->info('📧 Email: dataadmin@cityresq.com | Password: password123');
        $this->command->info('📧 Email: agency1@cityresq.com | Password: password123');
    }
}
