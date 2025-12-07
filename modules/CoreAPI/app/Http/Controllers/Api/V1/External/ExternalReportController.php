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

namespace App\Http\Controllers\Api\V1\External;

use App\Http\Controllers\Api\BaseController;
use App\Models\PhanAnh;
use App\Models\NguoiDung;
use App\Events\ReportCreatedEvent;
use App\Events\NewReportForAdmins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * External Report Controller
 * For external systems using JWT authentication
 */
class ExternalReportController extends BaseController
{
    /**
     * Create a new report via external API
     * POST /api/v1/external/reports
     *
     * Authentication: JWT Bearer Token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'required|string|max:2000',
            'danh_muc_id' => 'required|exists:danh_muc_phan_anhs,id',
            'uu_tien_id' => 'nullable|exists:muc_uu_tiens,id',
            'vi_do' => 'required|numeric|between:-90,90',
            'kinh_do' => 'required|numeric|between:-180,180',
            'dia_chi' => 'required|string|max:500',
            'la_cong_khai' => 'nullable|boolean',
            'the_tags' => 'nullable|array',
            'the_tags.*' => 'string|max:50',
            'nguoi_dung_id' => 'nullable|exists:nguoi_dungs,id', // Optional - for service-to-service
            'external_id' => 'nullable|string|max:100', // Track external system ID
            'external_system' => 'nullable|string|max:50', // e.g., "mobile_v2", "iot_sensor"
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors()->toArray());
        }

        // Determine user
        $authenticatedUser = auth('sanctum')->user();
        $userId = $request->filled('nguoi_dung_id')
            ? $request->nguoi_dung_id
            : $authenticatedUser->id;

        // Verify user exists and is active
        $user = NguoiDung::where('id', $userId)
            ->where('trang_thai', 1)
            ->first();

        if (!$user) {
            return $this->error('User not found or account disabled', 403);
        }

        // Default priority if not provided
        $uuTienId = $request->input('uu_tien_id', 2); // 2 = Trung bình (Medium)

        // Create report
        $report = PhanAnh::create([
            'nguoi_dung_id' => $userId,
            'tieu_de' => $request->tieu_de,
            'mo_ta' => $request->mo_ta,
            'danh_muc_id' => $request->danh_muc_id,
            'uu_tien_id' => $uuTienId,
            'vi_do' => $request->vi_do,
            'kinh_do' => $request->kinh_do,
            'dia_chi' => $request->dia_chi,
            'la_cong_khai' => $request->boolean('la_cong_khai', true),
            'trang_thai' => 0, // PENDING
            'luot_ung_ho' => 0,
            'luot_khong_ung_ho' => 0,
            'luot_xem' => 0,
            'the_tags' => $request->get('the_tags', []),
            'nhan_ai' => 'Pending classification',
            'do_tin_cay' => 0.0,
        ]);

        // External tracking via external_id parameter
        // (metadata field not in current schema, can be added later if needed)

        // Load relationships
        $report->load(['nguoiDung', 'danhMuc', 'uuTien']);

        // Dispatch event (same as mobile app)
        event(new ReportCreatedEvent($report, $user));

        // 🔥 Broadcast to all admins for realtime monitoring
        broadcast(new NewReportForAdmins($report, $user))->toOthers();

        Log::info('External report created via JWT', [
            'report_id' => $report->id,
            'user_id' => $userId,
            'external_id' => $request->input('external_id'),
            'external_system' => $request->input('external_system'),
            'auth_user_id' => $authenticatedUser->id,
        ]);

        return $this->created([
            'id' => $report->id,
            'tieu_de' => $report->tieu_de,
            'mo_ta' => $report->mo_ta,
            'danh_muc_id' => $report->danh_muc_id,
            'danh_muc' => $report->danhMuc ? [
                'id' => $report->danhMuc->id,
                'ten_danh_muc' => $report->danhMuc->ten_danh_muc,
            ] : null,
            'trang_thai' => $report->trang_thai,
            'uu_tien_id' => $report->uu_tien_id,
            'uu_tien' => $report->uuTien ? [
                'id' => $report->uuTien->id,
                'level' => $report->uuTien->cap_do,
                'ten_muc' => $report->uuTien->ten_muc,
                'mau_sac' => $report->uuTien->mau_sac,
            ] : null,
            'vi_do' => $report->vi_do,
            'kinh_do' => $report->kinh_do,
            'dia_chi' => $report->dia_chi,
            'external_id' => $request->input('external_id'),
            'created_at' => $report->created_at,
        ], 'Report created successfully via external API');
    }

    /**
     * Get report by ID or external_id
     * GET /api/v1/external/reports/{id}
     *
     * @param Request $request
     * @param mixed $id - Can be internal ID or external_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        // Try to find by ID first
        $report = PhanAnh::with(['nguoiDung', 'danhMuc', 'uuTien', 'coQuanXuLy', 'hinhAnhs'])
            ->find($id);

        // External ID lookup would require metadata field in database schema

        if (!$report) {
            return $this->error('Report not found', 404);
        }

        // Metadata tracking (future enhancement)

        return $this->success([
            'id' => $report->id,
            'tieu_de' => $report->tieu_de,
            'mo_ta' => $report->mo_ta,
            'trang_thai' => $report->trang_thai,
            'trang_thai_text' => $this->getStatusText($report->trang_thai),
            'danh_muc' => $report->danhMuc ? [
                'id' => $report->danhMuc->id,
                'ten_danh_muc' => $report->danhMuc->ten_danh_muc,
            ] : null,
            'uu_tien' => $report->uuTien ? [
                'id' => $report->uuTien->id,
                'level' => $report->uuTien->cap_do,
                'ten_muc' => $report->uuTien->ten_muc,
            ] : null,
            'vi_do' => $report->vi_do,
            'kinh_do' => $report->kinh_do,
            'dia_chi' => $report->dia_chi,
            'co_quan_xu_ly' => $report->coQuanXuLy ? [
                'id' => $report->coQuanXuLy->id,
                'ten_co_quan' => $report->coQuanXuLy->ten_co_quan,
            ] : null,
            'incident_id' => $report->incident_id,
            'external_id' => $request->input('external_id'),
            'external_system' => $request->input('external_system'),
            'created_at' => $report->created_at,
            'updated_at' => $report->updated_at,
        ]);
    }

    /**
     * List reports with filters
     * GET /api/v1/external/reports
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = PhanAnh::with(['nguoiDung', 'danhMuc', 'uuTien', 'coQuanXuLy']);

        // Filter by external_system (future: requires metadata field)

        // Filter by status
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Filter by category
        if ($request->filled('danh_muc_id')) {
            $query->where('danh_muc_id', $request->danh_muc_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reports = $query->paginate($request->input('per_page', 20));

        return $this->success($reports);
    }

    /**
     * Get status text
     */
    private function getStatusText($status)
    {
        $statuses = [
            0 => 'PENDING',
            1 => 'VERIFIED',
            2 => 'PROCESSING',
            3 => 'RESOLVED',
            4 => 'REJECTED',
        ];
        return $statuses[$status] ?? 'UNKNOWN';
    }
}
