<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding CityResQ360 Database...');
        $this->command->newLine();

        // Seed in correct order (respecting foreign keys)
        $this->call([
            QuanTriVienSeeder::class,
            CoQuanXuLySeeder::class,
            DanhMucPhanAnhSeeder::class,
            MucUuTienSeeder::class,
            NguoiDungSeeder::class,
            PhanAnhSeeder::class,
            CauHinhHeThongSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('🔐 Admin Login Credentials:');
        $this->command->info('   Email: superadmin@cityresq.com');
        $this->command->info('   Password: password123');
        $this->command->newLine();
        $this->command->info('👤 User Login Credentials:');
        $this->command->info('   Email: nguyenvanan@gmail.com');
        $this->command->info('   Password: password123');
    }
}
