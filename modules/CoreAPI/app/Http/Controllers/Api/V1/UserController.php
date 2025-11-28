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
use App\Models\NguoiDung;
use App\Models\PhanAnh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * User Controller
 * 
 * Handles user profile and statistics endpoints
 * - Public user profiles
 * - User reports
 * - User statistics
 * - Dashboard overview (authenticated user)
 * - Leaderboard
 * - City-wide stats
 */
class UserController extends BaseController
{
    /**
     * Get public user profile
     * 
     * GET /api/v1/users/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = NguoiDung::find($id);

        if (!$user) {
            return $this->notFound('Không tìm thấy người dùng');
        }

        $data = [
            'id' => $user->id,
            'ho_ten' => $user->ho_ten,
            'anh_dai_dien' => $user->anh_dai_dien,
            'cap_huy_hieu' => $user->cap_huy_hieu,
            'cap_huy_hieu_text' => $this->getBadgeText($user->cap_huy_hieu),
            'diem_uy_tin' => $user->diem_uy_tin ?? 0,
            'tong_so_phan_anh' => PhanAnh::where('nguoi_dung_id', $id)->count(),
            'ty_le_chinh_xac' => $this->calculateAccuracyRate($id),
            'ngay_tham_gia' => $user->created_at->toIso8601String(),
        ];

        return $this->success($data, 'Lấy thông tin người dùng thành công');
    }

    /**
     * Get user's public reports
     * 
     * GET /api/v1/users/{id}/reports
     * Query: ?page=1
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reports($id, Request $request)
    {
        $user = NguoiDung::find($id);

        if (!$user) {
            return $this->notFound('Không tìm thấy người dùng');
        }

        $reports = PhanAnh::with(['danhMuc:id,ten_danh_muc'])
            ->where('nguoi_dung_id', $id)
            ->where('la_cong_khai', true)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        $data = $reports->getCollection()->map(function ($report) {
            return [
                'id' => $report->id,
                'tieu_de' => $report->tieu_de,
                'danh_muc' => $report->danh_muc_id,
                'danh_muc_text' => $report->danhMuc->ten_danh_muc ?? 'Khác',
                'trang_thai' => $report->trang_thai,
                'vi_do' => (float) $report->vi_do,
                'kinh_do' => (float) $report->kinh_do,
                'ngay_tao' => $report->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'last_page' => $reports->lastPage(),
            ]
        ]);
    }

    /**
     * Get user statistics
     * 
     * GET /api/v1/users/{id}/stats
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats($id)
    {
        $user = NguoiDung::find($id);

        if (!$user) {
            return $this->notFound('Không tìm thấy người dùng');
        }

        $total = PhanAnh::where('nguoi_dung_id', $id)->count();
        $pending = PhanAnh::where('nguoi_dung_id', $id)->where('trang_thai', 0)->count();
        $processing = PhanAnh::where('nguoi_dung_id', $id)->where('trang_thai', 2)->count();
        $resolved = PhanAnh::where('nguoi_dung_id', $id)->where('trang_thai', 3)->count();
        $rejected = PhanAnh::where('nguoi_dung_id', $id)->where('trang_thai', 4)->count();

        $stats = [
            'tong_so_phan_anh' => $total,
            'cho_xu_ly' => $pending,
            'dang_xu_ly' => $processing,
            'da_giai_quyet' => $resolved,
            'tu_choi' => $rejected,
            'ty_le_chinh_xac' => $this->calculateAccuracyRate($id),
            'diem_uy_tin' => $user->diem_uy_tin ?? 0,
            'xep_hang' => $this->getUserRank($id),
        ];

        return $this->success($stats, 'Lấy thống kê người dùng thành công');
    }

    /**
     * Get current user's dashboard overview
     * 
     * GET /api/v1/stats/overview
     * (Protected - requires auth)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function overview(Request $request)
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return $this->unauthorized('Vui lòng đăng nhập');
        }

        $total = PhanAnh::where('nguoi_dung_id', $userId)->count();
        $pending = PhanAnh::where('nguoi_dung_id', $userId)->where('trang_thai', 0)->count();
        $processing = PhanAnh::where('nguoi_dung_id', $userId)->where('trang_thai', 2)->count();
        $resolved = PhanAnh::where('nguoi_dung_id', $userId)->where('trang_thai', 3)->count();
        $rejected = PhanAnh::where('nguoi_dung_id', $userId)->where('trang_thai', 4)->count();

        $overview = [
            'tong_so_phan_anh' => $total,
            'cho_xu_ly' => $pending,
            'dang_xu_ly' => $processing,
            'da_giai_quyet' => $resolved,
            'tu_choi' => $rejected,
            'ty_le_chinh_xac' => $this->calculateAccuracyRate($userId),
            'diem_uy_tin' => auth()->user()->diem_uy_tin ?? 0,
            'xep_hang' => $this->getUserRank($userId),
        ];

        return $this->success($overview, 'Lấy tổng quan thành công');
    }

    /**
     * Get reports by category (current user)
     * 
     * GET /api/v1/stats/categories
     * (Protected - requires auth)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoriesStats(Request $request)
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return $this->unauthorized('Vui lòng đăng nhập');
        }

        $categories = PhanAnh::select('danh_muc_id', DB::raw('COUNT(*) as total'))
            ->with('danhMuc:id,ten_danh_muc')
            ->where('nguoi_dung_id', $userId)
            ->groupBy('danh_muc_id')
            ->get()
            ->map(function ($cat) {
                return [
                    'danh_muc' => $cat->danh_muc_id,
                    'danh_muc_text' => $cat->danhMuc->ten_danh_muc ?? 'Khác',
                    'total' => $cat->total,
                ];
            });

        return $this->success($categories, 'Lấy thống kê danh mục thành công');
    }

    /**
     * Get timeline chart data (current user)
     * 
     * GET /api/v1/stats/timeline
     * Query: ?tu_ngay=2025-01-01&den_ngay=2025-11-30
     * (Protected - requires auth)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeline(Request $request)
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return $this->unauthorized('Vui lòng đăng nhập');
        }
        $fromDate = $request->get('tu_ngay', now()->subMonths(6)->format('Y-m-d'));
        $toDate = $request->get('den_ngay', now()->format('Y-m-d'));

        $timeline = PhanAnh::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('nguoi_dung_id', $userId)
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($day) {
                return [
                    'date' => $day->date,
                    'count' => $day->count,
                ];
            });

        return $this->success($timeline, 'Lấy dữ liệu timeline thành công');
    }

    /**
     * Get leaderboard (top users by reputation)
     * 
     * GET /api/v1/stats/leaderboard
     * Query: ?page=1&limit=50
     * (Public endpoint)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function leaderboard(Request $request)
    {
        $limit = min($request->get('limit', 50), 100);
        
        $leaderboard = NguoiDung::select('id', 'ho_ten', 'anh_dai_dien', 'cap_huy_hieu', 'diem_uy_tin')
            ->where('vai_tro', 0) // Only citizens
            ->orderBy('diem_uy_tin', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                $totalReports = PhanAnh::where('nguoi_dung_id', $user->id)->count();
                
                return [
                    'rank' => $index + 1,
                    'user' => [
                        'id' => $user->id,
                        'ho_ten' => $user->ho_ten,
                        'anh_dai_dien' => $user->anh_dai_dien,
                        'cap_huy_hieu' => $user->cap_huy_hieu,
                    ],
                    'diem_uy_tin' => $user->diem_uy_tin ?? 0,
                    'tong_so_phan_anh' => $totalReports,
                    'ty_le_chinh_xac' => $this->calculateAccuracyRate($user->id),
                ];
            });

        return $this->success($leaderboard, 'Lấy bảng xếp hạng thành công');
    }

    /**
     * Get city-wide public statistics
     * 
     * GET /api/v1/stats/city
     * (Public endpoint)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cityStats(Request $request)
    {
        $total = PhanAnh::count();
        $resolved = PhanAnh::where('trang_thai', 3)->count();
        $processing = PhanAnh::where('trang_thai', 2)->count();
        
        $resolutionRate = $total > 0 ? round(($resolved / $total) * 100, 2) : 0;

        // Average resolution time (hours) - thoi_gian_giai_quyet is already in hours
        $avgResolutionTime = PhanAnh::where('trang_thai', 3)
            ->whereNotNull('thoi_gian_giai_quyet')
            ->avg('thoi_gian_giai_quyet');

        // Top categories
        $topCategories = PhanAnh::select('danh_muc_id', DB::raw('COUNT(*) as total'))
            ->with('danhMuc:id,ten_danh_muc')
            ->groupBy('danh_muc_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($cat) {
                return [
                    'danh_muc' => $cat->danh_muc_id,
                    'danh_muc_text' => $cat->danhMuc->ten_danh_muc ?? 'Khác',
                    'total' => $cat->total,
                ];
            });

        $cityStats = [
            'tong_phan_anh' => $total,
            'da_giai_quyet' => $resolved,
            'dang_xu_ly' => $processing,
            'ty_le_giai_quyet' => $resolutionRate,
            'thoi_gian_xu_ly_trung_binh' => round($avgResolutionTime ?? 0, 2),
            'top_danh_muc' => $topCategories,
        ];

        return $this->success($cityStats, 'Lấy thống kê thành phố thành công');
    }

    /**
     * Calculate accuracy rate for user
     * 
     * @param int $userId
     * @return float
     */
    private function calculateAccuracyRate($userId)
    {
        $total = PhanAnh::where('nguoi_dung_id', $userId)->count();
        if ($total === 0) return 0;

        $accurate = PhanAnh::where('nguoi_dung_id', $userId)
            ->whereIn('trang_thai', [1, 2, 3]) // Confirmed, Processing, Resolved
            ->count();

        return round(($accurate / $total) * 100, 2);
    }

    /**
     * Get user rank based on reputation
     * 
     * @param int $userId
     * @return int
     */
    private function getUserRank($userId)
    {
        $user = NguoiDung::find($userId);
        if (!$user) return 0;

        $rank = NguoiDung::where('diem_uy_tin', '>', $user->diem_uy_tin ?? 0)
            ->where('vai_tro', 0)
            ->count();

        return $rank + 1;
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
}
