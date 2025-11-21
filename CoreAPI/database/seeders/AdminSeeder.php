<?php

namespace Database\Seeders;

use App\Models\QuanTriVien;
use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👤 Creating admin accounts...');
        DB::table('quan_tri_viens')->delete();
        DB::table('quan_tri_viens')->truncate();
        // Get roles from vai_tros table
        $superAdminRole     = VaiTro::where('slug', VaiTro::SLUG_SUPER_ADMIN)->first();
        $dataAdminRole      = VaiTro::where('slug', VaiTro::SLUG_DATA_ADMIN)->first();
        $agencyAdminRole    = VaiTro::where('slug', VaiTro::SLUG_AGENCY_ADMIN)->first();

        // Create Super Admin (is_master = true)
        QuanTriVien::create([
            'ten_quan_tri' => 'Super Admin',
            'email' => 'admin@master.com',
            'mat_khau' => '123456', // Auto-hashed by setMatKhauAttribute mutator
            'id_vai_tro' => $superAdminRole->id,
            'is_master' => true, // Only Super Admin has is_master = true
            'anh_dai_dien' => null,
            'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
            'lan_dang_nhap_cuoi' => now(),
        ]);
        $this->command->info('  ✓ Super Admin (is_master)');

        // Create Data Admin
        QuanTriVien::create([
            'ten_quan_tri' => 'Data Admin',
            'email' => 'dataadmin@cityresq360.com',
            'mat_khau' => 'password123',
            'id_vai_tro' => $dataAdminRole->id,
            'is_master' => false,
            'anh_dai_dien' => null,
            'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
            'lan_dang_nhap_cuoi' => now(),
        ]);
        $this->command->info('  ✓ Data Admin');

        // Create Agency Admin
        QuanTriVien::create([
            'ten_quan_tri' => 'Agency Admin',
            'email' => 'agencyadmin@cityresq360.com',
            'mat_khau' => 'password123',
            'id_vai_tro' => $agencyAdminRole->id,
            'is_master' => false,
            'anh_dai_dien' => null,
            'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
            'lan_dang_nhap_cuoi' => now(),
        ]);
        $this->command->info('  ✓ Agency Admin');

        $this->command->info('');
        $this->command->info('✅ Created 3 admin accounts (password: password123):');
        $this->command->info('   - superadmin@cityresq360.com (Super Admin - is_master)');
        $this->command->info('   - dataadmin@cityresq360.com (Data Admin)');
        $this->command->info('   - agencyadmin@cityresq360.com (Agency Admin)');
    }
}
