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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Model::unguard(); // Disable mass assignment protection

        $this->command->info('🌱 Seeding CityResQ360 Database...');
        $this->command->newLine();

        // Seed in correct order (respecting foreign keys)
        $this->call([
            // Admin & RBAC
            RBACSeeder::class,
            AdminSeeder::class,

            // Master data
            CoQuanXuLySeeder::class,
            DanhMucPhanAnhSeeder::class,
            MucUuTienSeeder::class,

            // Rewards (before transactions)
            PhanThuongSeeder::class,

            // Users & Reports
            NguoiDungSeeder::class,
            PhanAnhSeeder::class, // Use PhanAnhSeeder instead of ReportSeeder
            MediaSeeder::class,   // Add MediaSeeder

            // Comments (depends on Reports & Users)
            BinhLuanSeeder::class,

            // Wallet Transactions (depends on Users & Rewards)
            GiaoDichSeeder::class,

            // Notifications (depends on Users & Reports)
            ThongBaoSeeder::class,

            // System config
            CauHinhHeThongSeeder::class,

            // Module definitions (for API Gateway)
            ModuleDefinitionsSeeder::class,

            // Rewards

        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('🔐 Admin Login Credentials:');
        $this->command->info('   Super Admin:');
        $this->command->info('     Email: admin@master.com');
        $this->command->info('     Password: 123456');
        $this->command->info('   Data Admin / Agency Admin:');
        $this->command->info('     Email: dataadmin@cityresq360.com / agencyadmin@cityresq360.com');
        $this->command->info('     Password: password123');
        $this->command->newLine();
        $this->command->info('👤 Citizen Login Credentials:');
        $this->command->info('   Email: nguyenvanan@gmail.com (ID: 1)');
        $this->command->info('   Password: password123');
        $this->command->newLine();
        $this->command->info('📊 Database Summary:');
        $this->command->info('   - Admins: 3 (Super Admin, Data Admin, Agency Admin)');
        $this->command->info('   - Citizens: 10 users (IDs 1-10)');
        $this->command->info('   - Agencies: 12 (4 city, 6 district, 2 ward level)');
        $this->command->info('   - Reports: 50 (distributed over last 30 days)');
        $this->command->info('   - Comments: 10 (6 top-level, 4 replies)');
        $this->command->info('   - Transactions: 15 (CityPoints add/deduct)');
        $this->command->info('   - Notifications: 15 (read & unread)');
        $this->command->info('   - Rewards: 10 (vouchers, gifts, events)');
        $this->command->info('   - Categories: 6 (Traffic, Environment, Fire, Waste, Flood, Other)');
        $this->command->newLine();
        $this->command->info('🎯 All API endpoints now have test data!');

        Model::reguard(); // Re-enable mass assignment protection
    }
}
