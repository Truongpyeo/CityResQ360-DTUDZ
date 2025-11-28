<?php

namespace Database\Seeders;

use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NguoiDungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'ho_ten' => 'Nguyễn Văn An',
                'email' => 'nguyenvanan@gmail.com',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0901234567',
                'vai_tro' => NguoiDung::VAI_TRO_CITIZEN,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
                'diem_thanh_pho' => 500,
                'xac_thuc_cong_dan' => true,
                'diem_uy_tin' => 85,
                'tong_so_phan_anh' => 10,
                'so_phan_anh_chinh_xac' => 8,
                'ty_le_chinh_xac' => 80.0,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_GOLD,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => true,
                    'push' => true,
                    'sms' => false,
                ],
            ],
            // ID 2: Verified Citizen
            [
                'id' => 2,
                'ho_ten' => 'Trần Thị Bình',
                'email' => 'tranthib@gmail.com',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0907654321',
                'vai_tro' => NguoiDung::VAI_TRO_CITIZEN,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
                'diem_thanh_pho' => 350,
                'xac_thuc_cong_dan' => true,
                'diem_uy_tin' => 75,
                'tong_so_phan_anh' => 7,
                'so_phan_anh_chinh_xac' => 5,
                'ty_le_chinh_xac' => 71.43,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_SILVER,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => true,
                    'push' => true,
                    'sms' => true,
                ],
            ],
            // ID 3: Platinum User
            [
                'id' => 3,
                'ho_ten' => 'Lê Minh Cường',
                'email' => 'leminhcuong@gmail.com',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0912345678',
                'vai_tro' => NguoiDung::VAI_TRO_CITIZEN,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
                'diem_thanh_pho' => 800,
                'xac_thuc_cong_dan' => true,
                'diem_uy_tin' => 95,
                'tong_so_phan_anh' => 15,
                'so_phan_anh_chinh_xac' => 14,
                'ty_le_chinh_xac' => 93.33,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_PLATINUM,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => true,
                    'push' => true,
                    'sms' => false,
                ],
            ],

            // ID 4: Unverified Citizen
            [
                'id' => 4,
                'ho_ten' => 'Phạm Thị Dung',
                'email' => 'phamthidung@gmail.com',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0923456789',
                'vai_tro' => NguoiDung::VAI_TRO_CITIZEN,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
                'diem_thanh_pho' => 100,
                'xac_thuc_cong_dan' => false,
                'diem_uy_tin' => 50,
                'tong_so_phan_anh' => 2,
                'so_phan_anh_chinh_xac' => 1,
                'ty_le_chinh_xac' => 50.0,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_BRONZE,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => true,
                    'push' => false,
                    'sms' => false,
                ],
            ],
            // ID 5: New User
            [
                'id' => 5,
                'ho_ten' => 'Hoàng Văn Em',
                'email' => 'hoangvanem@gmail.com',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0934567890',
                'vai_tro' => NguoiDung::VAI_TRO_CITIZEN,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
                'diem_thanh_pho' => 50,
                'xac_thuc_cong_dan' => false,
                'diem_uy_tin' => 40,
                'tong_so_phan_anh' => 1,
                'so_phan_anh_chinh_xac' => 0,
                'ty_le_chinh_xac' => 0.0,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_BRONZE,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => false,
                    'push' => false,
                    'sms' => false,
                ],
            ],

            // ID 6: Officer 1
            [
                'id' => 6,
                'ho_ten' => 'Nguyễn Văn Phúc',
                'email' => 'officer1@tphcm.gov.vn',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0945678901',
                'vai_tro' => NguoiDung::VAI_TRO_OFFICER,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
                'diem_thanh_pho' => 0,
                'xac_thuc_cong_dan' => true,
                'diem_uy_tin' => 100,
                'tong_so_phan_anh' => 0,
                'so_phan_anh_chinh_xac' => 0,
                'ty_le_chinh_xac' => 0.0,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_BRONZE,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => true,
                    'push' => true,
                    'sms' => true,
                ],
            ],
            // ID 7: Officer 2
            [
                'id' => 7,
                'ho_ten' => 'Trần Thị Giang',
                'email' => 'officer2@tphcm.gov.vn',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0956789012',
                'vai_tro' => NguoiDung::VAI_TRO_OFFICER,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
                'diem_thanh_pho' => 0,
                'xac_thuc_cong_dan' => true,
                'diem_uy_tin' => 100,
                'tong_so_phan_anh' => 0,
                'so_phan_anh_chinh_xac' => 0,
                'ty_le_chinh_xac' => 0.0,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_BRONZE,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => true,
                    'push' => true,
                    'sms' => false,
                ],
            ],

            // ID 8: Banned User
            [
                'id' => 8,
                'ho_ten' => 'Người Dùng Bị Khóa',
                'email' => 'banned@gmail.com',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0967890123',
                'vai_tro' => NguoiDung::VAI_TRO_CITIZEN,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_BANNED,
                'diem_thanh_pho' => 0,
                'xac_thuc_cong_dan' => false,
                'diem_uy_tin' => 0,
                'tong_so_phan_anh' => 0,
                'so_phan_anh_chinh_xac' => 0,
                'ty_le_chinh_xac' => 0.0,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_BRONZE,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => false,
                    'push' => false,
                    'sms' => false,
                ],
            ],

            // ID 9: High Activity User 1
            [
                'id' => 9,
                'ho_ten' => 'Võ Thị Hoa',
                'email' => 'vothihoa@gmail.com',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0978901234',
                'vai_tro' => NguoiDung::VAI_TRO_CITIZEN,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
                'diem_thanh_pho' => 1200,
                'xac_thuc_cong_dan' => true,
                'diem_uy_tin' => 98,
                'tong_so_phan_anh' => 25,
                'so_phan_anh_chinh_xac' => 24,
                'ty_le_chinh_xac' => 96.0,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_PLATINUM,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => true,
                    'push' => true,
                    'sms' => true,
                ],
            ],
            // ID 10: High Activity User 2
            [
                'id' => 10,
                'ho_ten' => 'Đặng Minh Khôi',
                'email' => 'dangminhkhoi@gmail.com',
                'mat_khau' => Hash::make('password123'),
                'so_dien_thoai' => '0989012345',
                'vai_tro' => NguoiDung::VAI_TRO_CITIZEN,
                'anh_dai_dien' => null,
                'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
                'diem_thanh_pho' => 650,
                'xac_thuc_cong_dan' => true,
                'diem_uy_tin' => 88,
                'tong_so_phan_anh' => 12,
                'so_phan_anh_chinh_xac' => 10,
                'ty_le_chinh_xac' => 83.33,
                'cap_huy_hieu' => NguoiDung::HUY_HIEU_GOLD,
                'push_token' => null,
                'tuy_chon_thong_bao' => [
                    'email' => true,
                    'push' => true,
                    'sms' => false,
                ],
            ],
        ];

        // Delete existing users to ensure clean state
        NguoiDung::whereIn('id', array_column($users, 'id'))->forceDelete();
        NguoiDung::whereIn('email', array_column($users, 'email'))->forceDelete();

        foreach ($users as $user) {
            NguoiDung::create($user);
        }

        $this->command->info('✅ Created '.count($users).' users');
        $this->command->info('   - Verified citizens: 4 users');
        $this->command->info('   - Unverified citizens: 2 users');
        $this->command->info('   - Officers: 2 users');
        $this->command->info('   - High activity: 2 users');
        $this->command->info('📧 Test login: nguyenvanan@gmail.com | password123');
    }
}
