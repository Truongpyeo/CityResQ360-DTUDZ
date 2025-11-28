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
use App\Http\Requests\Api\Report\NearbyReportRequest;
use App\Http\Requests\Api\Report\RateReportRequest;
use App\Http\Requests\Api\Report\StoreReportRequest;
use App\Http\Requests\Api\Report\UpdateReportRequest;
use App\Models\DanhMucPhanAnh;
use App\Models\MucUuTien;
use App\Models\PhanAnh;
use Illuminate\Http\Request;

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

        // Filter by category (map index to ID)
        if ($request->filled('danh_muc')) {
            $categories = DanhMucPhanAnh::orderBy('thu_tu_hien_thi')->pluck('id')->toArray();
            $danhMucIndex = (int) $request->danh_muc;
            if ($danhMucIndex >= 0 && $danhMucIndex < count($categories)) {
                $query->where('danh_muc_id', $categories[$danhMucIndex]);
            }
        }

        // Filter by status
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Filter by priority (map index to ID)
        if ($request->filled('uu_tien')) {
            $priorities = MucUuTien::orderBy('cap_do')->pluck('id')->toArray();
            $uuTienIndex = (int) $request->uu_tien;
            if ($uuTienIndex >= 0 && $uuTienIndex < count($priorities)) {
                $query->where('uu_tien_id', $priorities[$uuTienIndex]);
            }
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tieu_de', 'like', "%{$search}%")
                    ->orWhere('mo_ta', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $reports = $query->paginate($perPage);

        return $this->paginated($reports);
    }

    /**
     * Create new report
     * POST /api/v1/reports
     */
    public function store(StoreReportRequest $request)
    {
        $user = $request->user();

        // Map danh_muc index (0-5) to actual database ID
        // API uses: 0=traffic, 1=environment, 2=fire, 3=waste, 4=flood, 5=other
        // Database IDs: 1, 2, 3, 4, 5, 6 (ordered by thu_tu_hien_thi)
        $categories = DanhMucPhanAnh::orderBy('thu_tu_hien_thi')->pluck('id')->toArray();
        $danhMucIndex = (int) $request->danh_muc;
        
        if ($danhMucIndex < 0 || $danhMucIndex >= count($categories)) {
            return $this->badRequest('Danh mục không hợp lệ');
        }
        
        $danhMucId = $categories[$danhMucIndex];

        // Map uu_tien index to actual database ID
        // API uses: 0=low, 1=medium, 2=high, 3=urgent
        $priorities = MucUuTien::orderBy('cap_do')->pluck('id')->toArray();
        $uuTienIndex = (int) $request->get('uu_tien', 1); // Default: medium (index 1)
        
        if ($uuTienIndex < 0 || $uuTienIndex >= count($priorities)) {
            $uuTienIndex = 1; // Default to medium if invalid
        }
        
        $uuTienId = $priorities[$uuTienIndex];

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
            'la_cong_khai' => $request->get('la_cong_khai', true),
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
                ->get();

            foreach ($mediaRecords as $media) {
                $mediaIds[] = $media->id;
            }
        }

        // TODO: Award points to user (+10 CityPoints)

        $report->load(['nguoiDung', 'danhMuc', 'uuTien']);

        // Load media if any
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
                'ten_muc' => $report->uuTien->ten_muc,
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
     * Get report detail
     * GET /api/v1/reports/{id}
     */
    public function show(Request $request, $id)
    {
        $report = PhanAnh::with(['nguoiDung', 'danhMuc', 'uuTien', 'coQuanXuLy', 'binhLuans.nguoiDung'])
            ->find($id);

        if (! $report) {
            return $this->notFound('Không tìm thấy phản ánh');
        }

        $user = $request->user();

        // Check if public or user is owner
        if (! $report->la_cong_khai && (! $user || $user->id !== $report->nguoi_dung_id)) {
            return $this->forbidden('Phản ánh này không công khai');
        }

        // Get vote status for authenticated user
        $userVoted = null;
        if ($user) {
            $vote = $report->binhChons()->where('nguoi_dung_id', $user->id)->first();
            $userVoted = $vote ? $vote->loai_binh_chon : null;
        }

        return $this->success([
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
                'ten_muc' => $report->uuTien->ten_muc,
            ] : null,
            'vi_do' => $report->vi_do,
            'kinh_do' => $report->kinh_do,
            'dia_chi' => $report->dia_chi,
            'luot_ung_ho' => $report->luot_ung_ho,
            'luot_khong_ung_ho' => $report->luot_khong_ung_ho,
            'luot_xem' => $report->luot_xem,
            'nhan_ai' => $report->nhan_ai,
            'do_tin_cay' => $report->do_tin_cay,
            'user' => $report->nguoiDung ? [
                'id' => $report->nguoiDung->id,
                'ho_ten' => $report->nguoiDung->ho_ten,
                'anh_dai_dien' => $report->nguoiDung->anh_dai_dien,
            ] : null,
            'agency' => $report->coQuanXuLy ? [
                'id' => $report->coQuanXuLy->id,
                'ten_co_quan' => $report->coQuanXuLy->ten_co_quan,
            ] : null,
            'votes' => [
                'total_upvotes' => $report->luot_ung_ho,
                'total_downvotes' => $report->luot_khong_ung_ho,
                'user_voted' => $userVoted,
            ],
            'comments' => $report->binhLuans->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'noi_dung' => $comment->noi_dung,
                    'user' => [
                        'id' => $comment->nguoiDung->id,
                        'ho_ten' => $comment->nguoiDung->ho_ten,
                        'anh_dai_dien' => $comment->nguoiDung->anh_dai_dien,
                    ],
                    'created_at' => $comment->created_at,
                ];
            }),
            'created_at' => $report->created_at,
            'updated_at' => $report->updated_at,
        ]);
    }

    /**
     * Update report (only owner)
     * PUT /api/v1/reports/{id}
     */
    public function update(UpdateReportRequest $request, $id)
    {
        $report = PhanAnh::find($id);

        if (! $report) {
            return $this->notFound('Không tìm thấy phản ánh');
        }

        // Check ownership
        if ($report->nguoi_dung_id !== $request->user()->id) {
            return $this->forbidden('Bạn không có quyền chỉnh sửa phản ánh này');
        }

        $report->update([
            'tieu_de' => $request->tieu_de ?? $report->tieu_de,
            'mo_ta' => $request->mo_ta ?? $report->mo_ta,
            'uu_tien_id' => $request->uu_tien_id ?? $report->uu_tien_id,
        ]);

        return $this->success([
            'id' => $report->id,
            'tieu_de' => $report->tieu_de,
            'mo_ta' => $report->mo_ta,
            'uu_tien_id' => $report->uu_tien_id,
            'uu_tien' => $report->uuTien ? [
                'id' => $report->uuTien->id,
                'ten_muc' => $report->uuTien->ten_muc,
            ] : null,
            'updated_at' => $report->updated_at,
        ], 'Cập nhật phản ánh thành công');
    }

    /**
     * Delete report (only owner)
     * DELETE /api/v1/reports/{id}
     */
    public function destroy(Request $request, $id)
    {
        $report = PhanAnh::find($id);

        if (! $report) {
            return $this->notFound('Không tìm thấy phản ánh');
        }

        // Check ownership
        if ($report->nguoi_dung_id !== $request->user()->id) {
            return $this->forbidden('Bạn không có quyền xóa phản ánh này');
        }

        // Can't delete if in progress or resolved
        if (in_array($report->trang_thai, [2, 3])) {
            return $this->error('Không thể xóa phản ánh đang xử lý hoặc đã giải quyết');
        }

        $report->delete();

        return $this->success([
            'id' => $id,
            'deleted' => true,
        ], 'Xóa phản ánh thành công');
    }

    /**
     * Get my reports
     * GET /api/v1/reports/my
     */
    public function myReports(Request $request)
    {
        $query = PhanAnh::with(['danhMuc', 'uuTien', 'coQuanXuLy'])
            ->where('nguoi_dung_id', $request->user()->id);

        // Filter by status
        if ($request->has('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Sort
        $query->orderBy('created_at', 'desc');

        // Paginate
        $perPage = $request->get('per_page', 15);
        $reports = $query->paginate($perPage);

        return $this->paginated($reports);
    }

    /**
     * Get nearby reports (location-based)
     * GET /api/v1/reports/nearby
     */
    public function nearby(NearbyReportRequest $request)
    {
        $lat = $request->vi_do;
        $lon = $request->kinh_do;
        $radius = $request->get('radius', 5); // Default 5km

        // Haversine formula for distance calculation
        $reports = PhanAnh::selectRaw('
                *,
                (6371 * acos(cos(radians(?))
                * cos(radians(vi_do))
                * cos(radians(kinh_do) - radians(?))
                + sin(radians(?))
                * sin(radians(vi_do)))) AS distance
            ', [$lat, $lon, $lat])
            ->where('la_cong_khai', true)
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->with(['nguoiDung', 'danhMuc', 'uuTien'])
            ->limit(50)
            ->get();

        return $this->success($reports->map(function ($report) {
            return [
                'id' => $report->id,
                'tieu_de' => $report->tieu_de,
                'danh_muc_id' => $report->danh_muc_id,
                'danh_muc' => $report->danhMuc ? [
                    'id' => $report->danhMuc->id,
                    'ten_danh_muc' => $report->danhMuc->ten_danh_muc,
                ] : null,
                'trang_thai' => $report->trang_thai,
                'uu_tien_id' => $report->uu_tien_id,
                'uu_tien' => $report->uuTien ? [
                    'id' => $report->uuTien->id,
                    'ten_muc' => $report->uuTien->ten_muc,
                ] : null,
                'vi_do' => $report->vi_do,
                'kinh_do' => $report->kinh_do,
                'dia_chi' => $report->dia_chi,
                'distance' => round($report->distance, 2),
                'created_at' => $report->created_at,
            ];
        }));
    }

    /**
     * Get trending reports (most upvotes)
     * GET /api/v1/reports/trending
     */
    public function trending(Request $request)
    {
        $limit = $request->get('limit', 10);

        $reports = PhanAnh::with(['nguoiDung', 'danhMuc', 'uuTien'])
            ->where('la_cong_khai', true)
            ->where('created_at', '>=', now()->subDays(7)) // Last 7 days
            ->orderBy('luot_ung_ho', 'desc')
            ->orderBy('luot_xem', 'desc')
            ->limit($limit)
            ->get();

        return $this->success($reports);
    }

    /**
     * Increment view count
     * POST /api/v1/reports/{id}/view
     */
    public function incrementView($id)
    {
        $report = PhanAnh::find($id);

        if (! $report) {
            return $this->notFound('Không tìm thấy phản ánh');
        }

        $report->increment('luot_xem');

        return $this->success([
            'luot_xem' => $report->luot_xem,
        ]);
    }

    /**
     * Rate report (after resolved)
     * POST /api/v1/reports/{id}/rate
     */
    public function rate(RateReportRequest $request, $id)
    {
        $report = PhanAnh::find($id);

        if (! $report) {
            return $this->notFound('Không tìm thấy phản ánh');
        }

        // Only owner can rate
        if ($report->nguoi_dung_id !== $request->user()->id) {
            return $this->forbidden('Chỉ người tạo phản ánh mới có thể đánh giá');
        }

        // Can only rate resolved reports
        if ($report->trang_thai !== 3) {
            return $this->error('Chỉ có thể đánh giá phản ánh đã được giải quyết');
        }

        $report->update([
            'danh_gia_hai_long' => $request->danh_gia_hai_long,
            'nhan_xet' => $request->get('nhan_xet'),
        ]);

        return $this->success([
            'danh_gia_hai_long' => $report->danh_gia_hai_long,
            'nhan_xet' => $report->nhan_xet,
        ], 'Đánh giá thành công. Cảm ơn bạn!');
    }
}
