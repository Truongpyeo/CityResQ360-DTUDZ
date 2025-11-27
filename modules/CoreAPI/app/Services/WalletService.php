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

namespace App\Services;

use App\Models\GiaoDichDiem;
use App\Models\NguoiDung;
use App\Models\PhanThuong;
use App\Models\DoiPhanThuong;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Add points to user wallet
     * 
     * @param int $userId
     * @param int $points
     * @param string $reason
     * @param string|null $linkedType
     * @param int|null $linkedId
     * @return GiaoDichDiem
     */
    public function addPoints(
        int $userId, 
        int $points, 
        string $reason, 
        string $linkedType = null, 
        int $linkedId = null
    ): GiaoDichDiem {
        return DB::transaction(function () use ($userId, $points, $reason, $linkedType, $linkedId) {
            $user = NguoiDung::lockForUpdate()->findOrFail($userId);
            
            $balanceBefore = $user->diem_thanh_pho ?? 0;
            $balanceAfter = $balanceBefore + $points;
            
            // Update user balance
            $user->diem_thanh_pho = $balanceAfter;
            
            // Update badge level if needed
            $user->cap_huy_hieu = $this->calculateBadgeLevel($balanceAfter);
            
            $user->save();
            
            // Create transaction record
            $transaction = GiaoDichDiem::create([
                'nguoi_dung_id' => $userId,
                'loai_giao_dich' => 0, // reward
                'so_diem' => $points,
                'so_du_truoc' => $balanceBefore,
                'so_du_sau' => $balanceAfter,
                'ly_do' => $reason,
                'lien_ket_loai' => $linkedType,
                'lien_ket_id' => $linkedId,
            ]);
            
            return $transaction;
        });
    }
    
    /**
     * Deduct points from user wallet
     * 
     * @param int $userId
     * @param int $points
     * @param string $reason
     * @param string|null $linkedType
     * @param int|null $linkedId
     * @return GiaoDichDiem
     * @throws \Exception
     */
    public function deductPoints(
        int $userId, 
        int $points, 
        string $reason,
        string $linkedType = null,
        int $linkedId = null
    ): GiaoDichDiem {
        return DB::transaction(function () use ($userId, $points, $reason, $linkedType, $linkedId) {
            $user = NguoiDung::lockForUpdate()->findOrFail($userId);
            
            $balanceBefore = $user->diem_thanh_pho ?? 0;
            
            if ($balanceBefore < $points) {
                throw new \Exception('Số điểm không đủ');
            }
            
            $balanceAfter = $balanceBefore - $points;
            
            // Update user balance
            $user->diem_thanh_pho = $balanceAfter;
            $user->save();
            
            // Create transaction record
            $transaction = GiaoDichDiem::create([
                'nguoi_dung_id' => $userId,
                'loai_giao_dich' => 1, // spend
                'so_diem' => -$points,
                'so_du_truoc' => $balanceBefore,
                'so_du_sau' => $balanceAfter,
                'ly_do' => $reason,
                'lien_ket_loai' => $linkedType,
                'lien_ket_id' => $linkedId,
            ]);
            
            return $transaction;
        });
    }
    
    /**
     * Get wallet balance
     * 
     * @param int $userId
     * @return array
     */
    public function getBalance(int $userId): array
    {
        $user = NguoiDung::findOrFail($userId);
        
        return [
            'diem_thanh_pho' => $user->diem_thanh_pho ?? 0,
            'diem_uy_tin' => $user->diem_uy_tin ?? 0,
            'cap_huy_hieu' => $user->cap_huy_hieu ?? 0,
        ];
    }
    
    /**
     * Get transaction history
     * 
     * @param int $userId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getTransactions(int $userId, array $filters = [])
    {
        $query = GiaoDichDiem::where('nguoi_dung_id', $userId);
        
        if (isset($filters['loai_giao_dich'])) {
            $query->where('loai_giao_dich', $filters['loai_giao_dich']);
        }
        
        return $query->orderBy('created_at', 'desc')
                    ->paginate($filters['per_page'] ?? 15);
    }
    
    /**
     * Calculate badge level based on total points
     * 
     * @param int $totalPoints
     * @return int
     */
    public function calculateBadgeLevel(int $totalPoints): int
    {
        if ($totalPoints >= 2000) return 4; // Diamond
        if ($totalPoints >= 1000) return 3; // Platinum
        if ($totalPoints >= 500) return 2;  // Gold
        if ($totalPoints >= 100) return 1;  // Silver
        return 0; // Bronze
    }
    
    /**
     * Check if user can redeem points
     * 
     * @param int $userId
     * @param int $requiredPoints
     * @return bool
     */
    public function canRedeem(int $userId, int $requiredPoints): bool
    {
        $user = NguoiDung::find($userId);
        return $user && ($user->diem_thanh_pho ?? 0) >= $requiredPoints;
    }
    
    /**
     * Process reward redemption
     * 
     * @param int $userId
     * @param int $rewardId
     * @return DoiPhanThuong
     * @throws \Exception
     */
    public function processRedeem(int $userId, int $rewardId): DoiPhanThuong
    {
        return DB::transaction(function () use ($userId, $rewardId) {
            $reward = PhanThuong::lockForUpdate()->findOrFail($rewardId);
            
            // Check availability
            if (!$reward->isAvailable()) {
                throw new \Exception('Phần thưởng không khả dụng');
            }
            
            // Check user balance
            if (!$this->canRedeem($userId, $reward->so_diem_can)) {
                throw new \Exception('Số điểm không đủ');
            }
            
            // Deduct points
            $this->deductPoints(
                $userId,
                $reward->so_diem_can,
                "Đổi phần thưởng: {$reward->ten_phan_thuong}",
                'doi_phan_thuong',
                null
            );
            
            // Decrease reward quantity
            $reward->so_luong_con_lai--;
            $reward->save();
            
            // Create redemption record
            $redemption = DoiPhanThuong::create([
                'nguoi_dung_id' => $userId,
                'phan_thuong_id' => $rewardId,
                'so_diem_su_dung' => $reward->so_diem_can,
                'ma_voucher' => $this->generateVoucherCode(),
                'trang_thai' => 1, // approved
            ]);
            
            // Update transaction link
            $lastTransaction = GiaoDichDiem::where('nguoi_dung_id', $userId)
                ->where('loai_giao_dich', 1)
                ->latest()
                ->first();
            
            if ($lastTransaction) {
                $lastTransaction->lien_ket_id = $redemption->id;
                $lastTransaction->save();
            }
            
            return $redemption;
        });
    }
    
    /**
     * Generate unique voucher code
     * 
     * @return string
     */
    private function generateVoucherCode(): string
    {
        return 'CITY' . date('Y') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }
    
    /**
     * Get next level points requirement
     * 
     * @param int $currentBadge
     * @return int
     */
    public function getNextLevelPoints(int $currentBadge): int
    {
        $levels = [
            0 => 100,  // Bronze → Silver
            1 => 500,  // Silver → Gold
            2 => 1000, // Gold → Platinum
            3 => 2000, // Platinum → Diamond
            4 => 0,    // Diamond (max level)
        ];
        
        return $levels[$currentBadge] ?? 100;
    }
}
