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
use App\Http\Requests\Api\Report\StoreReportRequest;
use App\Models\PhanAnh;
use Illuminate\Http\Request;
use App\Events\ReportCreatedEvent;
use App\Events\ReportUpdated;

class ReportController extends BaseController
{
    /**
     * List reports with filters
     * GET /api/v1/reports
     */
    public function index(Request $request)
    {
        $query = PhanAnh::with(['nguoiDung', 'danhMuc', 'uuTien', 'coQuanXuLy'])
            ->where('la_cong_khai', true);

        // Filter by category
        if ($request->filled('danh_muc_id')) {
            $query->where('danh_muc_id', $request->danh_muc_id);
        }

        // Filter by status
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Filter by priority
        if ($request->filled('uu_tien_id')) {
            $query->where('uu_tien_id', $request->uu_tien_id);
        }

        // Search by title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tieu_de', 'like', "%{$search}%")
                    ->orWhere('mo_ta', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Allow sorting by popular fields
        if (in_array($sortBy, ['created_at', 'luot_ung_ho', 'luot_xem'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $reports = $query->paginate($request->input('per_page', 20));

        return $this->success($reports);
    }

    /**
     * Create a new report
     * POST /api/v1/reports
     */
    public function store(StoreReportRequest $request)
    {
        $user = $request->user();

        // Use direct IDs from request
        // Default priority ID = 2 (Trung bình) if not provided
        $uuTienId = $request->input('uu_tien_id', $request->input('uu_tien', 2));
        $danhMucId = $request->input('danh_muc_id', $request->input('danh_muc'));

        // Create report
        $report = PhanAnh::create([
            'nguoi_dung_id' => $user->id,
            'tieu_de' => $request->tieu_de,
            'mo_ta' => $request->mo_ta,
            'danh_muc_id' => $danhMucId,
            'uu_tien_id' => $uuTienId,
            'vi_do' => $request->vi_do,
            'kinh_do' => $request->kinh_do,
            'dia_chi' => $request->dia_chi,
            'la_cong_khai' => $request->boolean('la_cong_khai', true),
            'trang_thai' => 0, // Pending
            'luot_ung_ho' => 0,
            'luot_khong_ung_ho' => 0,
            'luot_xem' => 0,
            'the_tags' => $request->get('the_tags', []),
            // TODO: AI Classification
            'nhan_ai' => 'Pending classification',
            'do_tin_cay' => 0.0,
        ]);

        // Attach media if provided
        $mediaIds = [];
        if ($request->has('media_ids') && is_array($request->media_ids) && count($request->media_ids) > 0) {
            // Validate and link media with this report
            $mediaRecords = \App\Models\HinhAnhPhanAnh::whereIn('id', $request->media_ids)
                ->where('nguoi_dung_id', $user->id)
                ->whereNull('phan_anh_id') // Only attach unlinked media
                ->get();

            foreach ($mediaRecords as $media) {
                // UPDATE: Link media to this report
                $media->update(['phan_anh_id' => $report->id]);
                $mediaIds[] = $media->id;
            }
        }

        // Load relationships for response
        $report->load(['nguoiDung', 'danhMuc', 'uuTien', 'hinhAnhs', 'coQuanXuLy']);

        // Dispatch event to RabbitMQ
        event(new \App\Events\ReportCreatedEvent($report, $user));

        // Prepare media for response
        $media = [];
        if (count($mediaIds) > 0) {
            $media = \App\Models\HinhAnhPhanAnh::whereIn('id', $mediaIds)->get()->map(function ($m) {
                return [
                    'id' => $m->id,
                    'url' => $m->duong_dan_hinh_anh,
                    'thumbnail_url' => $m->duong_dan_thumbnail,
                    'type' => $m->loai_file,
                ];
            });
        }

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
                'level' => $report->uuTien->cap_do,  // 0=low, 1=medium, 2=high, 3=urgent
                'ten_muc' => $report->uuTien->ten_muc,
                'mau_sac' => $report->uuTien->mau_sac,
            ] : null,
            'vi_do' => $report->vi_do,
            'kinh_do' => $report->kinh_do,
            'dia_chi' => $report->dia_chi,
            'nhan_ai' => $report->nhan_ai,
            'do_tin_cay' => $report->do_tin_cay,
            'media' => $media,
            'media_ids' => $mediaIds,
            'created_at' => $report->created_at,
        ], 'Tạo phản ánh thành công. Bạn nhận được +10 CityPoints!');
    }

    /**
     * Get report details
     * GET /api/v1/reports/{id}
     */
    public function show($id)
    {
        $report = PhanAnh::with(['nguoiDung', 'danhMuc', 'uuTien', 'hinhAnhs', 'coQuanXuLy', 'binhLuans.nguoiDung'])
            ->findOrFail($id);

        // Increment view count
        $report->increment('luot_xem');

        return $this->success($report);
    }

    /**
     * Update report (only owner can update if status is pending)
     * PUT /api/v1/reports/{id}
     */
    public function update(Request $request, $id)
    {
        $report = PhanAnh::findOrFail($id);

        // Check ownership
        if ($request->user()->id !== $report->nguoi_dung_id) {
            return $this->error('Unauthorized', 403);
        }

        // Check status (only pending reports can be updated by user)
        if ($report->trang_thai !== 0) {
            return $this->error('Cannot update report that is already being processed', 400);
        }

        $report->update($request->only([
            'tieu_de',
            'mo_ta',
            'danh_muc_id',
            'uu_tien_id',
            'vi_do',
            'kinh_do',
            'dia_chi',
            'la_cong_khai'
        ]));

        // Dispatch update event
        event(new ReportUpdated($report));

        return $this->success($report, 'Cập nhật phản ánh thành công');
    }

    /**
     * Delete report (only owner can delete if status is pending)
     * DELETE /api/v1/reports/{id}
     */
    public function destroy(Request $request, $id)
    {
        $report = PhanAnh::findOrFail($id);

        // Check ownership
        if ($request->user()->id !== $report->nguoi_dung_id) {
            return $this->error('Unauthorized', 403);
        }

        // Check status
        if ($report->trang_thai !== 0) {
            return $this->error('Cannot delete report that is already being processed', 400);
        }

        $report->delete();

        return $this->success(null, 'Xóa phản ánh thành công');
    }

    /**
     * Get reports by current user
     * GET /api/v1/reports/my-reports
     */
    public function myReports(Request $request)
    {
        $user = $request->user();

        $reports = PhanAnh::with(['danhMuc', 'uuTien', 'hinhAnhs'])
            ->where('nguoi_dung_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return $this->success($reports);
    }

    /**
     * Get nearby reports based on lat/lng
     * GET /api/v1/reports/nearby
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'sometimes|numeric|min:0.1|max:50', // km
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $radius = $request->input('radius', 5); // default 5km

        // Haversine formula for distance calculation
        $reports = PhanAnh::selectRaw("
                *,
                (6371 * acos(cos(radians(?)) 
                * cos(radians(CAST(vi_do AS DECIMAL(10,8)))) 
                * cos(radians(CAST(kinh_do AS DECIMAL(11,8))) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(CAST(vi_do AS DECIMAL(10,8)))))) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<', $radius)
            ->where('la_cong_khai', true)
            ->with(['nguoiDung', 'danhMuc', 'uuTien'])
            ->orderBy('distance', 'asc')
            ->paginate(20);

        return $this->success($reports);
    }

    /**
     * Get trending reports (most engagement in last 7 days)
     * GET /api/v1/reports/trending
     */
    public function trending(Request $request)
    {
        // Get reports from last 7 days, sorted by engagement score
        $reports = PhanAnh::with(['nguoiDung', 'danhMuc', 'uuTien', 'hinhAnhs'])
            ->where('la_cong_khai', true)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('*, (luot_ung_ho + luot_xem * 0.1) as engagement_score')
            ->orderBy('engagement_score', 'desc')
            ->paginate($request->input('per_page', 20));

        return $this->success($reports);
    }

    /**
     * Increment view count for a report
     * POST /api/v1/reports/{id}/increment-view
     */
    public function incrementView(Request $request, $id)
    {
        $report = PhanAnh::findOrFail($id);
        $report->increment('luot_xem');

        return $this->success([
            'luot_xem' => $report->luot_xem
        ], 'View count incremented');
    }

    /**
     * Rate a report
     * POST /api/v1/reports/{id}/rate  
     */
    public function rate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'sometimes|string|max:500'
        ]);

        $report = PhanAnh::findOrFail($id);
        $user = $request->user();

        // For now, just acknowledge the rating
        // TODO: Create DanhGiaPhanAnh model/table for storing ratings

        return $this->success([
            'report_id' => $id,
            'rating' => $request->rating,
            'feedback' => $request->input('feedback')
        ], 'Rating recorded successfully');
    }
}
