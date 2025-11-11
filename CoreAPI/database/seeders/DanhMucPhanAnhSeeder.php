<?php

namespace Database\Seeders;

use App\Models\DanhMucPhanAnh;
use Illuminate\Database\Seeder;

class DanhMucPhanAnhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'ten_danh_muc' => 'Giao thông',
                'ma_danh_muc' => 'traffic',
                'mo_ta' => 'Phản ánh về tình trạng giao thông, ùn tắc, đèn hỏng, hố sụt đường',
                'icon' => 'Car',
                'mau_sac' => '#EF4444', // red
                'thu_tu_hien_thi' => 1,
                'trang_thai' => true,
            ],
            [
                'ten_danh_muc' => 'Môi trường',
                'ma_danh_muc' => 'environment',
                'mo_ta' => 'Phản ánh về ô nhiễm môi trường, cây xanh, nước thải',
                'icon' => 'TreeDeciduous',
                'mau_sac' => '#10B981', // green
                'thu_tu_hien_thi' => 2,
                'trang_thai' => true,
            ],
            [
                'ten_danh_muc' => 'Cháy nổ',
                'ma_danh_muc' => 'fire',
                'mo_ta' => 'Phản ánh về cháy nổ, khẩn cấp',
                'icon' => 'Flame',
                'mau_sac' => '#F97316', // orange
                'thu_tu_hien_thi' => 3,
                'trang_thai' => true,
            ],
            [
                'ten_danh_muc' => 'Rác thải',
                'ma_danh_muc' => 'waste',
                'mo_ta' => 'Phản ánh về rác thải, vệ sinh công cộng',
                'icon' => 'Trash2',
                'mau_sac' => '#8B5CF6', // purple
                'thu_tu_hien_thi' => 4,
                'trang_thai' => true,
            ],
            [
                'ten_danh_muc' => 'Ngập lụt',
                'ma_danh_muc' => 'flood',
                'mo_ta' => 'Phản ánh về ngập lụt, thoát nước',
                'icon' => 'Waves',
                'mau_sac' => '#3B82F6', // blue
                'thu_tu_hien_thi' => 5,
                'trang_thai' => true,
            ],
            [
                'ten_danh_muc' => 'Khác',
                'ma_danh_muc' => 'other',
                'mo_ta' => 'Các phản ánh khác',
                'icon' => 'MoreHorizontal',
                'mau_sac' => '#6B7280', // gray
                'thu_tu_hien_thi' => 99,
                'trang_thai' => true,
            ],
        ];

        foreach ($categories as $category) {
            DanhMucPhanAnh::create($category);
        }

        $this->command->info('✅ Created ' . count($categories) . ' report categories');
    }
}
