<?php

namespace Database\Seeders;

use App\Models\PhanThuong;
use Illuminate\Database\Seeder;

class PhanThuongSeeder extends Seeder
{
    /**
     * Run the database seeds for Rewards
     * Creates rewards that users can redeem with CityPoints
     */
    public function run(): void
    {
        // Delete existing rewards
        PhanThuong::query()->delete();

        $rewards = [
            // Voucher Rewards
            [
                'id' => 1,
                'ten_phan_thuong' => 'Voucher giảm giá 10%',
                'mo_ta' => 'Voucher giảm 10% cho các dịch vụ công tại TP.HCM',
                'loai' => PhanThuong::LOAI_VOUCHER,
                'so_diem_can' => 100,
                'so_luong' => 100,
                'so_luong_con_lai' => 50,
                'ngay_het_han' => now()->addMonths(3),
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],
            [
                'id' => 2,
                'ten_phan_thuong' => 'Voucher giảm giá 20%',
                'mo_ta' => 'Voucher giảm 20% cho các dịch vụ công - dành cho thành viên Silver trở lên',
                'loai' => PhanThuong::LOAI_VOUCHER,
                'so_diem_can' => 200,
                'so_luong' => 50,
                'so_luong_con_lai' => 30,
                'ngay_het_han' => now()->addMonths(3),
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],
            [
                'id' => 3,
                'ten_phan_thuong' => 'Voucher giảm giá 50%',
                'mo_ta' => 'Voucher giảm 50% - đặc biệt cho thành viên Platinum',
                'loai' => PhanThuong::LOAI_VOUCHER,
                'so_diem_can' => 500,
                'so_luong' => 20,
                'so_luong_con_lai' => 10,
                'ngay_het_han' => now()->addMonths(2),
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],

            // Gift Rewards
            [
                'id' => 4,
                'ten_phan_thuong' => 'Áo thun CityResQ360',
                'mo_ta' => 'Áo thun kỷ niệm CityResQ360 - màu trắng, nhiều size',
                'loai' => PhanThuong::LOAI_QUA_TANG,
                'so_diem_can' => 300,
                'so_luong' => 50,
                'so_luong_con_lai' => 20,
                'ngay_het_han' => now()->addMonths(6),
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],
            [
                'id' => 5,
                'ten_phan_thuong' => 'Bình nước thể thao',
                'mo_ta' => 'Bình nước thể thao cao cấp với logo CityResQ360',
                'loai' => PhanThuong::LOAI_QUA_TANG,
                'so_diem_can' => 150,
                'so_luong' => 100,
                'so_luong_con_lai' => 35,
                'ngay_het_han' => now()->addMonths(4),
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],

            // Event Tickets
            [
                'id' => 6,
                'ten_phan_thuong' => 'Vé tham quan bảo tàng',
                'mo_ta' => 'Vé miễn phí tham quan Bảo tàng Thành phố Hồ Chí Minh',
                'loai' => PhanThuong::LOAI_VE_SU_KIEN,
                'so_diem_can' => 80,
                'so_luong' => 200,
                'so_luong_con_lai' => 100,
                'ngay_het_han' => now()->addMonths(1),
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],
            [
                'id' => 7,
                'ten_phan_thuong' => 'Vé tham dự sự kiện Smart City',
                'mo_ta' => 'Vé tham dự hội thảo "TP.HCM Thông Minh 2025"',
                'loai' => PhanThuong::LOAI_VE_SU_KIEN,
                'so_diem_can' => 250,
                'so_luong' => 50,
                'so_luong_con_lai' => 25,
                'ngay_het_han' => now()->addDays(30),
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],

            // Special Rewards
            [
                'id' => 8,
                'ten_phan_thuong' => 'Chứng nhận Công dân Mẫu mực',
                'mo_ta' => 'Chứng nhận Công dân Mẫu mực từ UBND TP.HCM',
                'loai' => PhanThuong::LOAI_CHUNG_NHAN,
                'so_diem_can' => 1000,
                'so_luong' => 10,
                'so_luong_con_lai' => 5,
                'ngay_het_han' => now()->addMonths(12),
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],
            [
                'id' => 9,
                'ten_phan_thuong' => 'Gặp gỡ Lãnh đạo thành phố',
                'mo_ta' => 'Cơ hội gặp gỡ và trao đổi trực tiếp với Lãnh đạo TP.HCM',
                'loai' => PhanThuong::LOAI_DIEU_KY_NIEM,
                'so_diem_can' => 2000,
                'so_luong' => 5,
                'so_luong_con_lai' => 2,
                'ngay_het_han' => now()->addMonths(6),
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],

            // Limited Time Offer
            [
                'id' => 10,
                'ten_phan_thuong' => 'Voucher ăn uống 100K',
                'mo_ta' => 'Voucher ăn uống 100.000đ tại các nhà hàng đối tác - Ưu đãi có hạn!',
                'loai' => PhanThuong::LOAI_VOUCHER,
                'so_diem_can' => 120,
                'so_luong' => 100,
                'so_luong_con_lai' => 50,
                'ngay_het_han' => now()->addDays(7), // Limited 7 days
                'trang_thai' => PhanThuong::TRANG_THAI_ACTIVE,
                'hinh_anh' => null,
            ],
        ];

        foreach ($rewards as $reward) {
            PhanThuong::create($reward);
        }

        $totalPoints = array_sum(array_column($rewards, 'so_diem_can')) * array_sum(array_column($rewards, 'so_luong_con_lai'));
        $avgPoints = round(array_sum(array_column($rewards, 'so_diem_can')) / count($rewards));

        $this->command->info('✅ Created '.count($rewards).' rewards');
        $this->command->info('   - Vouchers: 4');
        $this->command->info('   - Gifts: 2');
        $this->command->info('   - Event Tickets: 2');
        $this->command->info('   - Special Rewards: 2');
        $this->command->info('   - Average points: '.$avgPoints);
        $this->command->info('   - Total available items: '.array_sum(array_column($rewards, 'so_luong_con_lai')));
    }
}
