<?php

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
                ->get();

            foreach ($mediaRecords as $media) {
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
}
