<?php

namespace Database\Seeders;

use App\Models\MucUuTien;
use Illuminate\Database\Seeder;

class MucUuTienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            [
                'ten_muc' => 'Thấp',
                'ma_muc' => 'low',
                'mo_ta' => 'Vấn đề không khẩn cấp, có thể xử lý sau',
                'cap_do' => 0,
                'mau_sac' => '#10B981', // green
                'thoi_gian_phan_hoi_toi_da' => 168, // 7 days
                'trang_thai' => true,
            ],
            [
                'ten_muc' => 'Trung bình',
                'ma_muc' => 'medium',
                'mo_ta' => 'Vấn đề cần được xử lý trong thời gian hợp lý',
                'cap_do' => 1,
                'mau_sac' => '#F59E0B', // amber
                'thoi_gian_phan_hoi_toi_da' => 72, // 3 days
                'trang_thai' => true,
            ],
            [
                'ten_muc' => 'Cao',
                'ma_muc' => 'high',
                'mo_ta' => 'Vấn đề quan trọng, cần ưu tiên xử lý',
                'cap_do' => 2,
                'mau_sac' => '#F97316', // orange
                'thoi_gian_phan_hoi_toi_da' => 24, // 1 day
                'trang_thai' => true,
            ],
            [
                'ten_muc' => 'Khẩn cấp',
                'ma_muc' => 'urgent',
                'mo_ta' => 'Vấn đề cực kỳ nghiêm trọng, cần xử lý ngay lập tức',
                'cap_do' => 3,
                'mau_sac' => '#EF4444', // red
                'thoi_gian_phan_hoi_toi_da' => 4, // 4 hours
                'trang_thai' => true,
            ],
        ];

        foreach ($priorities as $priority) {
            MucUuTien::create($priority);
        }

        $this->command->info('✅ Created ' . count($priorities) . ' priority levels');
    }
}
