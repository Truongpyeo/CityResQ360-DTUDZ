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

use App\Models\PhanAnh;
use App\Models\NguoiDung;
use App\Models\CoQuanXuLy;
use App\Models\DanhMucPhanAnh;
use App\Models\MucUuTien;
use Illuminate\Database\Seeder;

class PhanAnhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = NguoiDung::where('vai_tro', NguoiDung::VAI_TRO_CITIZEN)
            ->where('trang_thai', NguoiDung::TRANG_THAI_ACTIVE)
            ->get();

        $agencies = CoQuanXuLy::where('trang_thai', CoQuanXuLy::TRANG_THAI_ACTIVE)->get();

        if ($users->isEmpty() || $agencies->isEmpty()) {
            $this->command->error('❌ Please run NguoiDungSeeder and CoQuanXuLySeeder first!');
            return;
        }

        // Get categories and priorities
        $trafficCat = DanhMucPhanAnh::where('ma_danh_muc', 'traffic')->first();
        $environmentCat = DanhMucPhanAnh::where('ma_danh_muc', 'environment')->first();
        $fireCat = DanhMucPhanAnh::where('ma_danh_muc', 'fire')->first();

        $highPriority = MucUuTien::where('ma_muc', 'high')->first();
        $urgentPriority = MucUuTien::where('ma_muc', 'urgent')->first();

        $reports = [
            // Traffic Reports
            [
                'nguoi_dung_id' => $users->random()->id,
                'tieu_de' => 'Ùn tắc giao thông nghiêm trọng đường Nguyễn Huệ',
                'mo_ta' => 'Đường Nguyễn Huệ đoạn gần Nhà hát Thành phố bị ùn tắc nghiêm trọng từ 17h đến 19h hàng ngày. Nhiều phương tiện chen chúc không lối thoát.',
                'danh_muc_id' => $trafficCat->id,
                'trang_thai' => PhanAnh::TRANG_THAI_PENDING,
                'uu_tien_id' => $highPriority->id,
                'vi_do' => 10.7763897,
                'kinh_do' => 106.7011391,
                'dia_chi' => 'Đường Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP.HCM',
                'nhan_ai' => ['traffic', 'congestion'],
                'do_tin_cay' => 0.85,
                'co_quan_phu_trach_id' => $agencies->where('cap_do', CoQuanXuLy::CAP_DO_CITY)->first()->id,
                'la_cong_khai' => true,
                'luot_ung_ho' => 45,
                'luot_khong_ung_ho' => 3,
                'luot_xem' => 250,
                'han_phan_hoi' => now()->addDays(3),
                'the_tags' => ['giao_thong', 'un_tac', 'quan_1'],
            ],
            [
                'nguoi_dung_id' => $users->random()->id,
                'tieu_de' => 'Đèn giao thông hỏng tại ngã tư Lê Lợi - Pasteur',
                'mo_ta' => 'Đèn tín hiệu giao thông tại ngã tư Lê Lợi - Pasteur đã hỏng 2 ngày nay, gây nguy hiểm cho người tham gia giao thông.',
                'danh_muc_id' => $trafficCat->id,
                'trang_thai' => PhanAnh::TRANG_THAI_IN_PROGRESS,
                'uu_tien_id' => $urgentPriority->id,
                'vi_do' => 10.7769331,
                'kinh_do' => 106.6977394,
                'dia_chi' => 'Ngã tư Lê Lợi - Pasteur, Quận 1, TP.HCM',
                'nhan_ai' => ['traffic', 'broken_light'],
                'do_tin_cay' => 0.92,
                'co_quan_phu_trach_id' => $agencies->where('cap_do', CoQuanXuLy::CAP_DO_DISTRICT)->first()->id,
                'la_cong_khai' => true,
                'luot_ung_ho' => 78,
                'luot_khong_ung_ho' => 1,
                'luot_xem' => 420,
                'han_phan_hoi' => now()->addDay(),
                'thoi_gian_phan_hoi_thuc_te' => 4,
                'the_tags' => ['giao_thong', 'den_tin_hieu', 'khan_cap'],
            ],

            // Environment Reports
            [
                'nguoi_dung_id' => $users->random()->id,
                'tieu_de' => 'Ô nhiễm không khí nghiêm trọng khu vực Phạm Ngũ Lão',
                'mo_ta' => 'Khu vực đường Phạm Ngũ Lão có mùi hôi thối nồng nặc, nghi do rò rỉ khí ga hoặc nước thải. Người dân khó thở, mắt cay.',
                'danh_muc_id' => $environmentCat->id,
                'trang_thai' => PhanAnh::TRANG_THAI_VERIFIED,
                'uu_tien_id' => $highPriority->id,
                'vi_do' => 10.7677895,
                'kinh_do' => 106.6923516,
                'dia_chi' => 'Phạm Ngũ Lão, Phường Phạm Ngũ Lão, Quận 1, TP.HCM',
                'nhan_ai' => ['environment', 'air_pollution'],
                'do_tin_cay' => 0.88,
                'co_quan_phu_trach_id' => $agencies->where('cap_do', CoQuanXuLy::CAP_DO_CITY)->skip(1)->first()->id,
                'la_cong_khai' => true,
                'luot_ung_ho' => 62,
                'luot_khong_ung_ho' => 5,
                'luot_xem' => 310,
                'han_phan_hoi' => now()->addDays(2),
                'the_tags' => ['moi_truong', 'o_nhiem', 'quan_1'],
            ],
            [
                'nguoi_dung_id' => $users->random()->id,
                'tieu_de' => 'Cây xanh bị gãy đổ chắn ngang đường Trần Hưng Đạo',
                'mo_ta' => 'Sau trận mưa lớn tối qua, một cây lớn bị đổ chắn ngang đường Trần Hưng Đạo đoạn gần Chợ Bến Thành, gây cản trở giao thông.',
                'danh_muc_id' => $environmentCat->id,
                'trang_thai' => PhanAnh::TRANG_THAI_RESOLVED,
                'uu_tien_id' => $urgentPriority->id,
                'vi_do' => 10.7722995,
                'kinh_do' => 106.6979351,
                'dia_chi' => 'Trần Hưng Đạo, Phường Bến Thành, Quận 1, TP.HCM',
                'nhan_ai' => ['environment', 'tree_fallen'],
                'do_tin_cay' => 0.95,
                'co_quan_phu_trach_id' => $agencies->where('cap_do', CoQuanXuLy::CAP_DO_DISTRICT)->skip(1)->first()->id,
                'la_cong_khai' => true,
                'luot_ung_ho' => 95,
                'luot_khong_ung_ho' => 0,
                'luot_xem' => 550,
                'thoi_gian_phan_hoi_thuc_te' => 2,
                'thoi_gian_giai_quyet' => 6,
                'danh_gia_hai_long' => 5,
                'the_tags' => ['moi_truong', 'cay_xanh', 'da_xu_ly'],
            ],

            // Fire
            [
                'nguoi_dung_id' => $users->random()->id,
                'tieu_de' => 'Cháy nhà dân tại hẻm 45 Nguyễn Trãi',
                'mo_ta' => 'Phát hiện khói lửa bốc lên từ căn nhà 3 tầng. Đã gọi 114.',
                'danh_muc_id' => $fireCat->id,
                'trang_thai' => PhanAnh::TRANG_THAI_RESOLVED,
                'uu_tien_id' => $urgentPriority->id,
                'vi_do' => 10.7637405,
                'kinh_do' => 106.6843246,
                'dia_chi' => 'Hẻm 45 Nguyễn Trãi, Quận 1, TP.HCM',
                'nhan_ai' => ['fire', 'emergency'],
                'do_tin_cay' => 0.98,
                'co_quan_phu_trach_id' => $agencies->first()->id,
                'la_cong_khai' => true,
                'luot_ung_ho' => 120,
                'luot_khong_ung_ho' => 0,
                'luot_xem' => 850,
                'thoi_gian_giai_quyet' => 3,
                'danh_gia_hai_long' => 5,
                'the_tags' => ['chay_no', 'khan_cap'],
            ],
            // Report 12: For Postman Tests (User 1)
            [
                'id' => 12,
                'nguoi_dung_id' => 1,
                'tieu_de' => 'Test Report for Postman',
                'mo_ta' => 'This is a test report created specifically for Postman API testing.',
                'danh_muc_id' => $trafficCat->id,
                'trang_thai' => PhanAnh::TRANG_THAI_PENDING,
                'uu_tien_id' => $highPriority->id,
                'vi_do' => 10.7769,
                'kinh_do' => 106.7009,
                'dia_chi' => 'Test Address, District 1, HCMC',
                'nhan_ai' => ['test'],
                'do_tin_cay' => 1.0,
                'co_quan_phu_trach_id' => $agencies->first()->id,
                'la_cong_khai' => true,
                'luot_ung_ho' => 10,
                'luot_khong_ung_ho' => 0,
                'luot_xem' => 100,
                'han_phan_hoi' => now()->addDays(3),
                'the_tags' => ['test', 'postman'],
            ],
            // Report 13: Resolved Report for Rating (User 1)
            [
                'id' => 13,
                'nguoi_dung_id' => 1,
                'tieu_de' => 'Resolved Report for Rating',
                'mo_ta' => 'This report is resolved and ready to be rated.',
                'danh_muc_id' => $environmentCat->id,
                'trang_thai' => PhanAnh::TRANG_THAI_RESOLVED,
                'uu_tien_id' => $urgentPriority->id,
                'vi_do' => 10.7722,
                'kinh_do' => 106.6979,
                'dia_chi' => 'Resolved Address, District 1, HCMC',
                'nhan_ai' => ['resolved'],
                'do_tin_cay' => 1.0,
                'co_quan_phu_trach_id' => $agencies->first()->id,
                'la_cong_khai' => true,
                'luot_ung_ho' => 5,
                'luot_khong_ung_ho' => 0,
                'luot_xem' => 50,
                'thoi_gian_giai_quyet' => 12,
                'danh_gia_hai_long' => null, // Ready for rating
                'the_tags' => ['resolved', 'rating'],
            ],

        ];

        foreach ($reports as $reportData) {
            PhanAnh::create($reportData);
        }

        $this->command->info('✅ Created ' . count($reports) . ' sample reports');
    }
}
