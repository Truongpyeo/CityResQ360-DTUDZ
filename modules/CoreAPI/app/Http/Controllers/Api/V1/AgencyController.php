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
use App\Models\CoQuanXuLy;
use App\Models\PhanAnh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Agency Controller
 * 
 * Public readonly endpoints for viewing agencies (government departments)
 * - List agencies
 * - Agency details
 * - Agency reports
 * - Agency statistics
 */
class AgencyController extends BaseController
{
    /**
     * List all agencies
     * 
     * GET /api/v1/agencies
     * Query: ?page=1&cap_do=0
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = CoQuanXuLy::query()
            ->where('trang_thai', 1); // Only active agencies

        // Filter by level (cap_do)
        if ($request->has('cap_do')) {
            $query->where('cap_do', $request->cap_do);
        }

        $agencies = $query->paginate($request->get('per_page', 15));

        $data = $agencies->getCollection()->map(function ($agency) {
            return [
                'id' => $agency->id,
                'ten_co_quan' => $agency->ten_co_quan,
                'email_lien_he' => $agency->email_lien_he,
                'so_dien_thoai' => $agency->so_dien_thoai,
                'dia_chi' => $agency->dia_chi,
                'cap_do' => $agency->cap_do,
                'cap_do_text' => $this->getLevelText($agency->cap_do),
                'trang_thai' => $agency->trang_thai,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $agencies->currentPage(),
                'per_page' => $agencies->perPage(),
                'total' => $agencies->total(),
                'last_page' => $agencies->lastPage(),
            ]
        ]);
    }

    /**
     * Get agency detail
     * 
     * GET /api/v1/agencies/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $agency = CoQuanXuLy::find($id);

        if (!$agency) {
            return $this->notFound('Không tìm thấy cơ quan');
        }

        $data = [
            'id' => $agency->id,
            'ten_co_quan' => $agency->ten_co_quan,
            'email_lien_he' => $agency->email_lien_he,
            'so_dien_thoai' => $agency->so_dien_thoai,
            'dia_chi' => $agency->dia_chi,
            'cap_do' => $agency->cap_do,
            'cap_do_text' => $this->getLevelText($agency->cap_do),
            'mo_ta' => $agency->mo_ta,
            'trang_thai' => $agency->trang_thai,
        ];

        return $this->success($data, 'Lấy thông tin cơ quan thành công');
    }

    /**
     * Get agency's public reports
     * 
     * GET /api/v1/agencies/{id}/reports
     * Query: ?page=1&trang_thai=3
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reports($id, Request $request)
    {
        $agency = CoQuanXuLy::find($id);

        if (!$agency) {
            return $this->notFound('Không tìm thấy cơ quan');
        }

        $query = PhanAnh::query()
            ->with(['nguoiDung:id,ho_ten,anh_dai_dien', 'danhMuc:id,ten_danh_muc'])
            ->where('co_quan_phu_trach_id', $id)
            ->where('la_cong_khai', true);

        // Filter by status
        if ($request->has('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $reports = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));

        $data = $reports->getCollection()->map(function ($report) {
            return [
                'id' => $report->id,
                'tieu_de' => $report->tieu_de,
                'mo_ta' => $report->mo_ta,
                'danh_muc' => $report->danh_muc_id,
                'danh_muc_text' => $report->danhMuc->ten_danh_muc ?? 'Khác',
                'trang_thai' => $report->trang_thai,
                'trang_thai_text' => $this->getStatusText($report->trang_thai),
                'vi_do' => (float) $report->vi_do,
                'kinh_do' => (float) $report->kinh_do,
                'dia_chi' => $report->dia_chi,
                'ngay_tao' => $report->created_at->toIso8601String(),
                'ngay_cap_nhat' => $report->updated_at->toIso8601String(),
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
     * Get agency statistics
     * 
     * GET /api/v1/agencies/{id}/stats
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats($id)
    {
        $agency = CoQuanXuLy::find($id);

        if (!$agency) {
            return $this->notFound('Không tìm thấy cơ quan');
        }

        $total = PhanAnh::where('co_quan_phu_trach_id', $id)->count();
        $resolved = PhanAnh::where('co_quan_phu_trach_id', $id)->where('trang_thai', 3)->count();
        $processing = PhanAnh::where('co_quan_phu_trach_id', $id)->where('trang_thai', 2)->count();

        // Calculate resolution rate
        $resolutionRate = $total > 0 ? round(($resolved / $total) * 100, 2) : 0;

        // Calculate average response time (in minutes)
        $avgResponseTime = PhanAnh::where('co_quan_phu_trach_id', $id)
            ->whereNotNull('thoi_gian_phan_hoi_thuc_te')
            ->avg('thoi_gian_phan_hoi_thuc_te');

        // Calculate average resolution time (in hours)
        $avgResolutionTimeMinutes = PhanAnh::where('co_quan_phu_trach_id', $id)
            ->where('trang_thai', 3)
            ->whereNotNull('thoi_gian_giai_quyet')
            ->avg('thoi_gian_giai_quyet');
            
        $avgResolutionTime = $avgResolutionTimeMinutes ? ($avgResolutionTimeMinutes / 60) : 0;

        $stats = [
            'tong_phan_anh' => $total,
            'da_giai_quyet' => $resolved,
            'dang_xu_ly' => $processing,
            'ty_le_giai_quyet' => $resolutionRate,
            'thoi_gian_phan_hoi_trung_binh' => round($avgResponseTime ?? 0, 2), // minutes
            'thoi_gian_giai_quyet_trung_binh' => round($avgResolutionTime ?? 0, 2), // hours
        ];

        return $this->success($stats, 'Lấy thống kê cơ quan thành công');
    }

    /**
     * Get level text
     * 
     * @param int $level
     * @return string
     */
    private function getLevelText($level)
    {
        $levels = [
            0 => 'Trung ương',
            1 => 'Tỉnh/Thành phố',
            2 => 'Quận/Huyện',
            3 => 'Phường/Xã',
        ];

        return $levels[$level] ?? 'Khác';
    }

    /**
     * Get status text
     * 
     * @param int $status
     * @return string
     */
    private function getStatusText($status)
    {
        $statuses = [
            0 => 'Chờ xử lý',
            1 => 'Đã xác nhận',
            2 => 'Đang xử lý',
            3 => 'Đã giải quyết',
            4 => 'Từ chối',
        ];

        return $statuses[$status] ?? 'Không xác định';
    }
}
