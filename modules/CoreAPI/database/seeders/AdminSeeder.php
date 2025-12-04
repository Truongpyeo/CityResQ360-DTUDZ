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
use App\Models\VaiTro;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👤 Creating admin accounts...');

        // Get roles from vai_tros table
        $superAdminRole = VaiTro::where('slug', VaiTro::SLUG_SUPER_ADMIN)->first();
        $dataAdminRole = VaiTro::where('slug', VaiTro::SLUG_DATA_ADMIN)->first();
        $agencyAdminRole = VaiTro::where('slug', VaiTro::SLUG_AGENCY_ADMIN)->first();

        if (! $superAdminRole || ! $dataAdminRole || ! $agencyAdminRole) {
            $this->command->error('❌ Roles not found! Please run RBACSeeder first.');

            return;
        }

        // Create or update Super Admin (is_master = true)
        $superAdmin = QuanTriVien::updateOrCreate(
            ['email' => 'admin@master.com'],
            [
                'ten_quan_tri' => 'Super Admin',
                'mat_khau' => '123456', // Auto-hashed by setMatKhauAttribute mutator
                'id_vai_tro' => $superAdminRole->id,
                'is_master' => true, // Only Super Admin has is_master = true
                'anh_dai_dien' => null,
                'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
                'lan_dang_nhap_cuoi' => now(),
            ]
        );
        $this->command->info('  ✓ Super Admin (is_master) - '.($superAdmin->wasRecentlyCreated ? 'created' : 'updated'));

        // Create or update Data Admin
        $dataAdmin = QuanTriVien::updateOrCreate(
            ['email' => 'dataadmin@cityresq360.com'],
            [
                'ten_quan_tri' => 'Data Admin',
                'mat_khau' => 'password123',
                'id_vai_tro' => $dataAdminRole->id,
                'is_master' => false,
                'anh_dai_dien' => null,
                'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
                'lan_dang_nhap_cuoi' => now(),
            ]
        );
        $this->command->info('  ✓ Data Admin - '.($dataAdmin->wasRecentlyCreated ? 'created' : 'updated'));

        // Create or update Agency Admin
        $agencyAdmin = QuanTriVien::updateOrCreate(
            ['email' => 'agencyadmin@cityresq360.com'],
            [
                'ten_quan_tri' => 'Agency Admin',
                'mat_khau' => 'password123',
                'id_vai_tro' => $agencyAdminRole->id,
                'is_master' => false,
                'anh_dai_dien' => null,
                'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
                'lan_dang_nhap_cuoi' => now(),
            ]
        );
        $this->command->info('  ✓ Agency Admin - '.($agencyAdmin->wasRecentlyCreated ? 'created' : 'updated'));

        $this->command->info('');
        $this->command->info('✅ Created 3 admin accounts:');
        $this->command->info('   - admin@master.com (Super Admin - is_master, password: 123456)');
        $this->command->info('   - dataadmin@cityresq360.com (Data Admin, password: password123)');
        $this->command->info('   - agencyadmin@cityresq360.com (Agency Admin, password: password123)');
    }
}
