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

use App\Models\ThongBao;
use Illuminate\Database\Seeder;

class ThongBaoSeeder extends Seeder
{
    /**
     * Run the database seeds for Notifications
     */
    public function run(): void
    {
        // Delete existing notifications
        ThongBao::query()->delete();

        $notifications = [
            [
                'id' => 1,
                'nguoi_dung_id' => 1,
                'tieu_de' => 'Báo cáo của bạn đã được tiếp nhận',
                'noi_dung' => 'Báo cáo "Ổ gà đường Nguyễn Văn Linh" đã được cơ quan chức năng tiếp nhận và đang xử lý.',
                'loai' => ThongBao::LOAI_BAO_CAO,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 1, 'tham_chieu_loai' => 'phan_anh']),
                'da_doc' => true,
            ],
            [
                'id' => 2,
                'nguoi_dung_id' => 1,
                'tieu_de' => 'Bạn nhận được +50 CityPoints',
                'noi_dung' => 'Chúc mừng! Bạn nhận được 50 CityPoints cho báo cáo chính xác.',
                'loai' => ThongBao::LOAI_DIEM_THUONG,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 1, 'tham_chieu_loai' => 'giao_dich']),
                'da_doc' => true,
            ],
            [
                'id' => 3,
                'nguoi_dung_id' => 1,
                'tieu_de' => 'Báo cáo đã được xử lý xong',
                'noi_dung' => 'Báo cáo "Ổ gà đường Nguyễn Văn Linh" đã được xử lý thành công. Cảm ơn bạn đã đóng góp!',
                'loai' => ThongBao::LOAI_BAO_CAO,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 1, 'tham_chieu_loai' => 'phan_anh']),
                'da_doc' => false,
            ],
            [
                'id' => 4,
                'nguoi_dung_id' => 1,
                'tieu_de' => 'Bình luận mới trên báo cáo của bạn',
                'noi_dung' => 'Trần Thị Bình đã bình luận: "Tôi đồng ý với bạn! Đây là vấn đề cấp bách."',
                'loai' => ThongBao::LOAI_BINH_LUAN,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 2, 'tham_chieu_loai' => 'binh_luan']),
                'da_doc' => false,
            ],

            // Notifications for User 2 (Trần Thị Bình)
            [
                'id' => 5,
                'nguoi_dung_id' => 2,
                'tieu_de' => 'Báo cáo của bạn đã được tiếp nhận',
                'noi_dung' => 'Báo cáo đã được cơ quan chức năng tiếp nhận và đang xử lý.',
                'loai' => ThongBao::LOAI_BAO_CAO,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 3, 'tham_chieu_loai' => 'phan_anh']),
                'da_doc' => true,
            ],
            [
                'id' => 6,
                'nguoi_dung_id' => 2,
                'tieu_de' => 'Bạn nhận được +50 CityPoints',
                'noi_dung' => 'Chúc mừng! Bạn nhận được 50 CityPoints cho báo cáo chính xác.',
                'loai' => ThongBao::LOAI_DIEM_THUONG,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 5, 'tham_chieu_loai' => 'giao_dich']),
                'da_doc' => false,
            ],

            // Notifications for User 3 (Lê Minh Cường)
            [
                'id' => 7,
                'nguoi_dung_id' => 3,
                'tieu_de' => 'Chúc mừng! Bạn đạt huy hiệu Platinum',
                'noi_dung' => 'Bạn đã đạt huy hiệu Platinum với 15 báo cáo chính xác. Nhận ngay 100 CityPoints!',
                'loai' => ThongBao::LOAI_HUY_HIEU,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => null, 'tham_chieu_loai' => 'huy_hieu']),
                'da_doc' => true,
            ],
            [
                'id' => 8,
                'nguoi_dung_id' => 3,
                'tieu_de' => 'Báo cáo của bạn nhận nhiều upvote',
                'noi_dung' => 'Báo cáo của bạn đã nhận được 20 upvotes. Cảm ơn sự đóng góp của bạn!',
                'loai' => ThongBao::LOAI_BAO_CAO,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 4, 'tham_chieu_loai' => 'phan_anh']),
                'da_doc' => false,
            ],

            // Notifications for User 9 (Võ Thị Hoa)
            [
                'id' => 9,
                'nguoi_dung_id' => 9,
                'tieu_de' => 'Bạn là Top Contributor tháng này!',
                'noi_dung' => 'Chúc mừng! Bạn là người đóng góp nhiều nhất tháng này với 25 báo cáo. Nhận ngay 150 CityPoints!',
                'loai' => ThongBao::LOAI_KHEN_THUONG,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => null, 'tham_chieu_loai' => 'khen_thuong']),
                'da_doc' => false,
            ],
            [
                'id' => 10,
                'nguoi_dung_id' => 9,
                'tieu_de' => 'Báo cáo của bạn đã được xử lý',
                'noi_dung' => 'Báo cáo đã được xử lý thành công trong 24 giờ. Cảm ơn bạn!',
                'loai' => ThongBao::LOAI_BAO_CAO,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 5, 'tham_chieu_loai' => 'phan_anh']),
                'da_doc' => true,
            ],

            // Notifications for User 4 (Phạm Thị Dung) 
            [
                'id' => 11,
                'nguoi_dung_id' => 4,
                'tieu_de' => 'Chào mừng bạn đến với CityResQ360!',
                'noi_dung' => 'Cảm ơn bạn đã tham gia. Nhận ngay 25 CityPoints chào mừng!',
                'loai' => ThongBao::LOAI_HE_THONG,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => null, 'tham_chieu_loai' => null]),
                'da_doc' => true,
            ],
            [
                'id' => 12,
                'nguoi_dung_id' => 4,
                'tieu_de' => 'Báo cáo đầu tiên của bạn!',
                'noi_dung' => 'Chúc mừng báo cáo đầu tiên! Nhận thêm 30 CityPoints.',
                'loai' => ThongBao::LOAI_DIEM_THUONG,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 15, 'tham_chieu_loai' => 'giao_dich']),
                'da_doc' => false,
            ],

            // System notifications for User 10
            [
                'id' => 13,
                'nguoi_dung_id' => 10,
                'tieu_de' => 'Cập nhật chính sách mới',
                'noi_dung' => 'Chúng tôi đã cập nhật chính sách quyền riêng tư. Vui lòng xem chi tiết.',
                'loai' => ThongBao::LOAI_HE_THONG,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => null, 'tham_chieu_loai' => null]),
                'da_doc' => false,
            ],

            // Reminder notification for User 1
            [
                'id' => 14,
                'nguoi_dung_id' => 1,
                'tieu_de' => 'Bạn có thể nhận thưởng',
                'noi_dung' => 'Bạn đã đủ điểm để đổi voucher 10%. Hãy kiểm tra ngay!',
                'loai' => ThongBao::LOAI_PHAN_THUONG,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => null, 'tham_chieu_loai' => 'phan_thuong']),
                'da_doc' => false,
            ],

            // Report status update for User 2
            [
                'id' => 15,
                'nguoi_dung_id' => 2,
                'tieu_de' => 'Báo cáo đang được xử lý',
                'noi_dung' => 'Cơ quan chức năng đang xử lý báo cáo của bạn. Tiến độ: 70%.',
                'loai' => ThongBao::LOAI_BAO_CAO,
                'du_lieu_mo_rong' => json_encode(['tham_chieu_id' => 3, 'tham_chieu_loai' => 'phan_anh']),
                'da_doc' => false,
            ],
        ];

        foreach ($notifications as $notification) {
            ThongBao::create($notification);
        }

        $unreadCount = count(array_filter($notifications, fn($n) => !$n['da_doc']));

        $this->command->info('✅ Created '.count($notifications).' notifications');
        $this->command->info('   - Read: '.(count($notifications) - $unreadCount));
        $this->command->info('   - Unread: '.$unreadCount);
        $this->command->info('   - Types: Report, Points, Badge, System, Reward');
    }
}
