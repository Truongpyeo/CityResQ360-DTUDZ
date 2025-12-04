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

use App\Models\GiaoDichDiem;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;

class GiaoDichSeeder extends Seeder
{
    /**
     * Run the database seeds for Wallet Transactions
     * Creates test transactions for CityPoint system
     */
    public function run(): void
    {
        // Delete existing transactions
        GiaoDichDiem::query()->delete();

        $transactions = [
            // Transactions for User 1 (Nguyễn Văn An - ID 1)
            [
                'nguoi_dung_id' => 1,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 50,
                'ly_do' => 'Thưởng tạo báo cáo chính xác',
                'lien_ket_id' => 1, // Report ID
                'lien_ket_loai' => 'phan_anh',
            ],
            [
                'nguoi_dung_id' => 1,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 30,
                'ly_do' => 'Thưởng báo cáo được xử lý nhanh',
                'lien_ket_id' => 2,
                'lien_ket_loai' => 'phan_anh',
            ],
            [
                'nguoi_dung_id' => 1,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 20,
                'ly_do' => 'Thưởng tương tác tích cực (bình luận)',
                'lien_ket_id' => NULL,
                'lien_ket_loai' => 'binh_luan',
            ],
            [
                'nguoi_dung_id' => 1,
                'loai_giao_dich' => GiaoDichDiem::LOAI_TRU_DIEM,
                'so_diem' => 100,
                'ly_do' => 'Đổi voucher giảm giá 10%',
                'lien_ket_id' => 1,
                'lien_ket_loai' => 'phan_thuong',
            ],

            // Transactions for User 2 (Trần Thị Bình - ID 2)
            [
                'nguoi_dung_id' => 2,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 50,
                'ly_do' => 'Thưởng tạo báo cáo chính xác',
                'lien_ket_id' => 3,
                'lien_ket_loai' => 'phan_anh',
            ],
            [
                'nguoi_dung_id' => 2,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 25,
                'ly_do' => 'Thưởng báo cáo nhận nhiều upvote',
                'lien_ket_id' => 3,
                'lien_ket_loai' => 'phan_anh',
            ],

            // Transactions for User 3 (Lê Minh Cường - ID 3)
            [
                'nguoi_dung_id' => 3,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 100,
                'ly_do' => 'Thưởng đạt huy hiệu Platinum',
                'lien_ket_id' => NULL,
                'lien_ket_loai' => 'huy_hieu',
            ],
            [
                'nguoi_dung_id' => 3,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 50,
                'ly_do' => 'Thưởng tạo báo cáo chính xác',
                'lien_ket_id' => 4,
                'lien_ket_loai' => 'phan_anh',
            ],
            [
                'nguoi_dung_id' => 3,
                'loai_giao_dich' => GiaoDichDiem::LOAI_TRU_DIEM,
                'so_diem' => 200,
                'ly_do' => 'Đổi voucher giảm giá 20%',
                'lien_ket_id' => 2,
                'lien_ket_loai' => 'phan_thuong',
            ],

            // Transactions for User 9 (Võ Thị Hoa - ID 9)
            [
                'nguoi_dung_id' => 9,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 150,
                'ly_do' => 'Thưởng top contributor tháng',
                'lien_ket_id' => NULL,
                'lien_ket_loai' => 'khen_thuong',
            ],
            [
                'nguoi_dung_id' => 9,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 50,
                'ly_do' => 'Thưởng tạo báo cáo chính xác',
                'lien_ket_id' => 5,
                'lien_ket_loai' => 'phan_anh',
            ],

            // Transactions for User 10 (Đặng Minh Khôi - ID 10)
            [
                'nguoi_dung_id' => 10,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 50,
                'ly_do' => 'Thưởng tạo báo cáo chính xác',
                'lien_ket_id' => 6,
                'lien_ket_loai' => 'phan_anh',
            ],
            [
                'nguoi_dung_id' => 10,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 30,
                'ly_do' => 'Thưởng tương tác tích cực',
                'lien_ket_id' => NULL,
                'lien_ket_loai' => 'binh_luan',
            ],

            // Transactions for User 4 (Phạm Thị Dung - ID 4)
            [
                'nguoi_dung_id' => 4,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 25,
                'ly_do' => 'Thưởng người dùng mới',
                'lien_ket_id' => NULL,
                'lien_ket_loai' => 'dang_ky',
            ],
            [
                'nguoi_dung_id' => 4,
                'loai_giao_dich' => GiaoDichDiem::LOAI_CONG_DIEM,
                'so_diem' => 30,
                'ly_do' => 'Thưởng tạo báo cáo đầu tiên',
                'lien_ket_id' => 7,
                'lien_ket_loai' => 'phan_anh',
            ],
        ];

        // Process transactions by user to calculate balances
        $userBalances = [];

        foreach ($transactions as $transaction) {
            $userId = $transaction['nguoi_dung_id'];
            
            if (!isset($userBalances[$userId])) {
                $userBalances[$userId] = 0;
            }

            $currentBalance = $userBalances[$userId];
            $points = $transaction['so_diem'];
            $type = $transaction['loai_giao_dich'];

            $newBalance = $type == GiaoDichDiem::LOAI_CONG_DIEM 
                ? $currentBalance + $points 
                : $currentBalance - $points;

            $transaction['so_du_truoc'] = $currentBalance;
            $transaction['so_du_sau'] = $newBalance;
            
            // Update running balance
            $userBalances[$userId] = $newBalance;

            GiaoDichDiem::create($transaction);
        }

        $totalPoints = array_reduce($transactions, function($sum, $t) {
            return $sum + ($t['loai_giao_dich'] == GiaoDichDiem::LOAI_CONG_DIEM ? $t['so_diem'] : -$t['so_diem']);
        }, 0);

        $this->command->info('✅ Created '.count($transactions).' wallet transactions');
        $this->command->info('   - Add points: '.count(array_filter($transactions, fn($t) => $t['loai_giao_dich'] == GiaoDichDiem::LOAI_CONG_DIEM)));
        $this->command->info('   - Deduct points: '.count(array_filter($transactions, fn($t) => $t['loai_giao_dich'] == GiaoDichDiem::LOAI_TRU_DIEM)));
        $this->command->info('   - Net points distributed: '.$totalPoints);
    }
}
