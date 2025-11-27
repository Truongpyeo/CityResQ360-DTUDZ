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

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Models\PhanThuong;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Wallet Controller
 * 
 * Handles CityPoints (gamification system)
 * - Get balance
 * - Transaction history
 * - Redeem points
 * - Rewards catalog
 */
class WalletController extends BaseController
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    /**
     * Get wallet balance
     * 
     * GET /api/v1/wallet
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function balance(Request $request)
    {
        $user = auth()->user();

        $balance = [
            'diem_thanh_pho' => $user->diem_thanh_pho ?? 0,
            'diem_uy_tin' => $user->diem_uy_tin ?? 0,
            'cap_huy_hieu' => $user->cap_huy_hieu ?? 0,
            'cap_huy_hieu_text' => $this->getBadgeText($user->cap_huy_hieu),
            'next_level_points' => $this->getNextLevelPoints($user->cap_huy_hieu),
            'progress_percentage' => $this->getProgressPercentage($user->diem_thanh_pho, $user->cap_huy_hieu),
        ];

        return $this->success($balance, 'Lấy số dư ví thành công');
    }

    /**
     * Get transaction history
     * 
     * GET /api/v1/wallet/transactions
     * Query: ?page=1&loai_giao_dich=0
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactions(Request $request)
    {
        $userId = auth()->id();
        
        $filters = [
            'loai_giao_dich' => $request->loai_giao_dich,
            'per_page' => $request->get('per_page', 15),
        ];
        
        $transactions = $this->walletService->getTransactions($userId, $filters);
        
        $data = $transactions->getCollection()->map(function ($trans) {
            return [
                'id' => $trans->id,
                'loai_giao_dich' => $trans->loai_giao_dich,
                'loai_giao_dich_text' => $trans->loai_giao_dich_text,
                'so_diem' => $trans->so_diem,
                'so_du_truoc' => $trans->so_du_truoc,
                'so_du_sau' => $trans->so_du_sau,
                'ly_do' => $trans->ly_do,
                'ngay_tao' => $trans->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'last_page' => $transactions->lastPage(),
            ]
        ]);
    }

    /**
     * Redeem points (spend)
     * 
     * POST /api/v1/wallet/redeem
     * Body: { "phan_thuong_id": 5, "so_diem": 100 }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'phan_thuong_id' => 'required|integer|exists:phan_thuongs,id',
        ]);

        $userId = auth()->id();
        $rewardId = $request->phan_thuong_id;

        try {
            $redemption = $this->walletService->processRedeem($userId, $rewardId);
            
            $data = [
                'so_du_moi' => auth()->user()->fresh()->diem_thanh_pho,
                'voucher_code' => $redemption->ma_voucher,
                'phan_thuong' => $redemption->phanThuong->ten_phan_thuong,
            ];

            return $this->success($data, 'Đổi điểm thành công!');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    /**
     * Get available rewards catalog
     * 
     * GET /api/v1/wallet/rewards
     * Query: ?page=1&loai=0
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rewards(Request $request)
    {
        $query = PhanThuong::where('trang_thai', 1)
            ->where('so_luong_con_lai', '>', 0);
        
        // Filter by type if provided
        if ($request->has('loai')) {
            $query->where('loai', $request->loai);
        }
        
        // Filter out expired rewards
        $query->where(function ($q) {
            $q->whereNull('ngay_het_han')
              ->orWhere('ngay_het_han', '>=', now()->toDateString());
        });

        $rewards = $query->orderBy('so_diem_can')
            ->paginate($request->get('per_page', 15));
        
        $data = $rewards->getCollection()->map(function ($reward) {
            return [
                'id' => $reward->id,
                'ten_phan_thuong' => $reward->ten_phan_thuong,
                'mo_ta' => $reward->mo_ta,
                'loai' => $reward->loai,
                'loai_text' => $reward->loai_text,
                'so_diem_can' => $reward->so_diem_can,
                'hinh_anh' => $reward->hinh_anh,
                'so_luong_con_lai' => $reward->so_luong_con_lai,
                'ngay_het_han' => $reward->ngay_het_han?->format('Y-m-d'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $rewards->currentPage(),
                'per_page' => $rewards->perPage(),
                'total' => $rewards->total(),
                'last_page' => $rewards->lastPage(),
            ]
        ]);
    }

    /**
     * Get badge text
     * 
     * @param int|null $badge
     * @return string
     */
    private function getBadgeText($badge)
    {
        $badges = [
            0 => 'Đồng',
            1 => 'Bạc',
            2 => 'Vàng',
            3 => 'Bạch kim',
            4 => 'Kim cương',
        ];

        return $badges[$badge] ?? 'Chưa có';
    }

    /**
     * Get points required for next level
     * 
     * @param int|null $currentBadge
     * @return int
     */
    private function getNextLevelPoints($currentBadge)
    {
        $levels = [
            0 => 100,  // Bronze needs 100 points to reach Silver
            1 => 500,  // Silver needs 500 to reach Gold
            2 => 1000, // Gold needs 1000 to reach Platinum
            3 => 2000, // Platinum needs 2000 to reach Diamond
            4 => 0,    // Diamond is max level
        ];

        return $levels[$currentBadge] ?? 100;
    }

    /**
     * Get progress percentage to next level
     * 
     * @param int|null $currentPoints
     * @param int|null $currentBadge
     * @return float
     */
    private function getProgressPercentage($currentPoints, $currentBadge)
    {
        $currentPoints = $currentPoints ?? 0;
        $nextLevelPoints = $this->getNextLevelPoints($currentBadge);

        if ($nextLevelPoints === 0) {
            return 100; // Max level achieved
        }

        return min(round(($currentPoints / $nextLevelPoints) * 100, 2), 100);
    }
}
