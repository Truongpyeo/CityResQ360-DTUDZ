<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhanAnh;
use App\Models\CoQuanXuLy;
use App\Models\NhatKyHeThong;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display list of reports
     */
    public function index(Request $request)
    {
        $query = PhanAnh::with(['nguoiDung', 'coQuanXuLy']);

        // Filter by status
        if ($request->has('trang_thai') && $request->trang_thai !== '') {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Filter by category
        if ($request->has('danh_muc') && $request->danh_muc !== '') {
            $query->where('danh_muc', $request->danh_muc);
        }

        // Filter by priority
        if ($request->has('uu_tien') && $request->uu_tien !== '') {
            $query->where('uu_tien', $request->uu_tien);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tieu_de', 'like', "%{$search}%")
                    ->orWhere('mo_ta', 'like', "%{$search}%")
                    ->orWhere('dia_chi', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reports = $query->paginate(20)->through(function ($report) {
            return [
                'id' => $report->id,
                'tieu_de' => $report->tieu_de,
                'mo_ta' => $report->mo_ta,
                'danh_muc' => $report->danh_muc,
                'danh_muc_text' => $report->getCategoryName(),
                'trang_thai' => $report->trang_thai,
                'trang_thai_text' => $report->getStatusName(),
                'uu_tien' => $report->uu_tien,
                'uu_tien_text' => $report->getPriorityName(),
                'dia_chi' => $report->dia_chi,
                'vi_do' => $report->vi_do,
                'kinh_do' => $report->kinh_do,
                'do_tin_cay' => $report->do_tin_cay,
                'luot_ung_ho' => $report->luot_ung_ho,
                'luot_khong_ung_ho' => $report->luot_khong_ung_ho,
                'luot_xem' => $report->luot_xem,
                'nguoi_dung' => [
                    'id' => $report->nguoiDung?->id,
                    'ho_ten' => $report->nguoiDung?->ho_ten,
                    'email' => $report->nguoiDung?->email,
                ],
                'co_quan' => [
                    'id' => $report->coQuanXuLy?->id,
                    'ten_co_quan' => $report->coQuanXuLy?->ten_co_quan,
                ],
                'created_at' => $report->created_at->format('d/m/Y H:i'),
                'updated_at' => $report->updated_at->format('d/m/Y H:i'),
            ];
        });

        return Inertia::render('admin/reports/Index', [
            'reports' => $reports,
            'filters' => $request->only(['trang_thai', 'danh_muc', 'uu_tien', 'search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display report details
     */
    public function show($id)
    {
        $report = PhanAnh::with(['nguoiDung', 'coQuanXuLy', 'binhLuans.nguoiDung', 'binhChons'])
            ->findOrFail($id);

        return Inertia::render('admin/reports/Show', [
            'report' => [
                'id' => $report->id,
                'tieu_de' => $report->tieu_de,
                'mo_ta' => $report->mo_ta,
                'danh_muc' => $report->danh_muc,
                'danh_muc_text' => $report->getCategoryName(),
                'trang_thai' => $report->trang_thai,
                'trang_thai_text' => $report->getStatusName(),
                'uu_tien' => $report->uu_tien,
                'uu_tien_text' => $report->getPriorityName(),
                'dia_chi' => $report->dia_chi,
                'vi_do' => $report->vi_do,
                'kinh_do' => $report->kinh_do,
                'nhan_ai' => $report->nhan_ai,
                'do_tin_cay' => $report->do_tin_cay,
                'la_cong_khai' => $report->la_cong_khai,
                'luot_ung_ho' => $report->luot_ung_ho,
                'luot_khong_ung_ho' => $report->luot_khong_ung_ho,
                'luot_xem' => $report->luot_xem,
                'the_tags' => $report->the_tags,
                'du_lieu_mo_rong' => $report->du_lieu_mo_rong,
                'nguoi_dung' => [
                    'id' => $report->nguoiDung->id,
                    'ho_ten' => $report->nguoiDung->ho_ten,
                    'email' => $report->nguoiDung->email,
                    'so_dien_thoai' => $report->nguoiDung->so_dien_thoai,
                    'diem_uy_tin' => $report->nguoiDung->diem_uy_tin,
                    'xac_thuc_cong_dan' => $report->nguoiDung->xac_thuc_cong_dan,
                ],
                'co_quan' => $report->coQuanXuLy ? [
                    'id' => $report->coQuanXuLy->id,
                    'ten_co_quan' => $report->coQuanXuLy->ten_co_quan,
                    'email_lien_he' => $report->coQuanXuLy->email_lien_he,
                    'so_dien_thoai' => $report->coQuanXuLy->so_dien_thoai,
                ] : null,
                'binh_luans' => $report->binhLuans->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'noi_dung' => $comment->noi_dung,
                        'la_chinh_thuc' => $comment->la_chinh_thuc,
                        'nguoi_dung' => [
                            'ho_ten' => $comment->nguoiDung->ho_ten,
                        ],
                        'created_at' => $comment->created_at->format('d/m/Y H:i'),
                    ];
                }),
                'created_at' => $report->created_at->format('d/m/Y H:i'),
                'updated_at' => $report->updated_at->format('d/m/Y H:i'),
            ],
            'agencies' => CoQuanXuLy::where('trang_thai', CoQuanXuLy::TRANG_THAI_ACTIVE)
                ->get()
                ->map(function ($agency) {
                    return [
                        'id' => $agency->id,
                        'ten_co_quan' => $agency->ten_co_quan,
                    ];
                }),
        ]);
    }

    /**
     * Update report status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => ['required', 'integer', 'in:0,1,2,3,4'],
            'co_quan_phu_trach_id' => ['nullable', 'exists:co_quan_xu_lys,id'],
            'ghi_chu' => ['nullable', 'string', 'max:500'],
        ]);

        $report = PhanAnh::findOrFail($id);
        $oldStatus = $report->trang_thai;

        $report->update([
            'trang_thai' => $request->trang_thai,
            'co_quan_phu_trach_id' => $request->co_quan_phu_trach_id,
        ]);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_UPDATE,
            NhatKyHeThong::LOAI_PHAN_ANH,
            $report->id,
            [
                'old_status' => $oldStatus,
                'new_status' => $request->trang_thai,
                'ghi_chu' => $request->ghi_chu,
            ]
        );

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công!');
    }

    /**
     * Update report priority
     */
    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'uu_tien' => ['required', 'integer', 'in:0,1,2,3'],
        ]);

        $report = PhanAnh::findOrFail($id);
        $report->update(['uu_tien' => $request->uu_tien]);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_UPDATE,
            NhatKyHeThong::LOAI_PHAN_ANH,
            $report->id,
            ['action' => 'update_priority', 'uu_tien' => $request->uu_tien]
        );

        return redirect()->back()->with('success', 'Cập nhật độ ưu tiên thành công!');
    }

    /**
     * Delete report
     */
    public function destroy($id)
    {
        $report = PhanAnh::findOrFail($id);

        // Log before delete
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_DELETE,
            NhatKyHeThong::LOAI_PHAN_ANH,
            $report->id,
            ['tieu_de' => $report->tieu_de]
        );

        $report->delete();

        return redirect()->route('admin.reports.index')->with('success', 'Xóa phản ánh thành công!');
    }
}
