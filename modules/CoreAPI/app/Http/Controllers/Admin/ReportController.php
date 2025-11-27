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



namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhanAnh;
use App\Models\CoQuanXuLy;
use App\Models\DanhMucPhanAnh;
use App\Models\MucUuTien;
use App\Models\NhatKyHeThong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display list of reports
     */
    public function index(Request $request)
    {
        $query = PhanAnh::with(['nguoiDung', 'coQuanXuLy', 'danhMuc', 'uuTien']);

        // Filter by status
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Filter by category
        if ($request->filled('danh_muc_id')) {
            $query->where('danh_muc_id', $request->danh_muc_id);
        }

        // Filter by priority
        if ($request->filled('uu_tien_id')) {
            $query->where('uu_tien_id', $request->uu_tien_id);
        }

        // Filter by agency
        if ($request->filled('co_quan_phu_trach_id')) {
            $query->where('co_quan_phu_trach_id', $request->co_quan_phu_trach_id);
        }

        // Search
        if ($request->filled('search')) {
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
                'mo_ta' => substr($report->mo_ta, 0, 100) . '...',
                'danh_muc' => $report->danhMuc?->ten_danh_muc,
                'trang_thai' => $report->trang_thai,
                'trang_thai_text' => $report->getStatusName(),
                'uu_tien' => $report->uuTien?->ten_muc,
                'dia_chi' => $report->dia_chi,
                'vi_do' => $report->vi_do,
                'kinh_do' => $report->kinh_do,
                'do_tin_cay' => $report->do_tin_cay,
                'luot_ung_ho' => $report->luot_ung_ho,
                'luot_khong_ung_ho' => $report->luot_khong_ung_ho,
                'luot_xem' => $report->luot_xem,
                'nguoi_dung' => $report->nguoiDung?->ho_ten,
                'co_quan' => $report->coQuanXuLy?->ten_co_quan,
                'created_at' => $report->created_at->format('d/m/Y H:i'),
                'updated_at' => $report->updated_at->format('d/m/Y H:i'),
            ];
        });

        // Get stats for dashboard cards
        $stats = [
            'total' => PhanAnh::count(),
            'pending' => PhanAnh::where('trang_thai', PhanAnh::TRANG_THAI_PENDING)->count(),
            'in_progress' => PhanAnh::where('trang_thai', PhanAnh::TRANG_THAI_IN_PROGRESS)->count(),
            'resolved' => PhanAnh::where('trang_thai', PhanAnh::TRANG_THAI_RESOLVED)->count(),
        ];

        // Get categories for filter
        $categories = DanhMucPhanAnh::where('trang_thai', true)
            ->orderBy('thu_tu_hien_thi')
            ->get(['id', 'ten_danh_muc', 'ma_danh_muc']);

        // Get priorities for filter
        $priorities = MucUuTien::where('trang_thai', true)
            ->orderBy('cap_do')
            ->get(['id', 'ten_muc', 'ma_muc']);

        // Get agencies for filter
        $agencies = CoQuanXuLy::where('trang_thai', CoQuanXuLy::TRANG_THAI_ACTIVE)
            ->orderBy('ten_co_quan')
            ->get(['id', 'ten_co_quan']);

        return Inertia::render('admin/reports/Index', [
            'reports' => $reports,
            'stats' => $stats,
            'categories' => $categories,
            'priorities' => $priorities,
            'agencies' => $agencies,
            'filters' => $request->only(['trang_thai', 'danh_muc_id', 'uu_tien_id', 'co_quan_phu_trach_id', 'search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display report details
     */
    public function show($id)
    {
        $report = PhanAnh::with(['nguoiDung', 'coQuanXuLy', 'danhMuc', 'uuTien', 'binhLuans.nguoiDung', 'binhChons'])
                         ->findOrFail($id);

        return Inertia::render('admin/reports/Show', [
            'report' => [
                'id' => $report->id,
                'tieu_de' => $report->tieu_de,
                'mo_ta' => $report->mo_ta,
                'danh_muc' => [
                    'id' => $report->danhMuc?->id,
                    'ten_danh_muc' => $report->danhMuc?->ten_danh_muc,
                ],
                'trang_thai' => $report->trang_thai,
                'trang_thai_text' => $report->getStatusName(),
                'uu_tien' => [
                    'id' => $report->uuTien?->id,
                    'ten_muc' => $report->uuTien?->ten_muc,
                ],
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
        $oldAgency = $report->co_quan_phu_trach_id;

        $report->update([
            'trang_thai' => $request->trang_thai,
            'co_quan_phu_trach_id' => $request->co_quan_phu_trach_id,
        ]);

        // Log activity
        $admin = auth()->guard('admin')->user();
        NhatKyHeThong::create([
            'nguoi_thuc_hien_id' => $admin->id,
            'loai_nguoi_thuc_hien' => 'admin',
            'hanh_dong' => 'update_status',
            'mo_ta' => "Cập nhật trạng thái phản ánh #{$report->id}: {$report->getStatusName()}",
            'dia_chi_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'du_lieu_cu' => json_encode([
                'trang_thai' => $oldStatus,
                'co_quan_phu_trach_id' => $oldAgency,
            ]),
            'du_lieu_moi' => json_encode([
                'trang_thai' => $request->trang_thai,
                'co_quan_phu_trach_id' => $request->co_quan_phu_trach_id,
                'ghi_chu' => $request->ghi_chu,
            ]),
        ]);

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công!');
    }

    /**
     * Update report priority
     */
    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'uu_tien_id' => ['required', 'exists:muc_uu_tiens,id'],
        ]);

        $report = PhanAnh::findOrFail($id);
        $oldPriority = $report->uu_tien_id;

        $report->update(['uu_tien_id' => $request->uu_tien_id]);

        // Log activity
        $admin = auth()->guard('admin')->user();
        NhatKyHeThong::create([
            'nguoi_thuc_hien_id' => $admin->id,
            'loai_nguoi_thuc_hien' => 'admin',
            'hanh_dong' => 'update_priority',
            'mo_ta' => "Cập nhật độ ưu tiên phản ánh #{$report->id}",
            'dia_chi_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'du_lieu_cu' => json_encode(['uu_tien_id' => $oldPriority]),
            'du_lieu_moi' => json_encode(['uu_tien_id' => $request->uu_tien_id]),
        ]);

        return redirect()->back()->with('success', 'Cập nhật độ ưu tiên thành công!');
    }

    /**
     * Delete report
     */
    public function destroy($id)
    {
        $report = PhanAnh::findOrFail($id);

        // Authorization check
        if (Gate::forUser(auth()->guard('admin')->user())->denies('delete', $report)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa phản ánh này!');
        }

        $admin = auth()->guard('admin')->user();

        // Log before delete
        NhatKyHeThong::create([
            'nguoi_thuc_hien_id' => $admin->id,
            'loai_nguoi_thuc_hien' => 'admin',
            'hanh_dong' => 'delete_report',
            'mo_ta' => "Xóa phản ánh #{$report->id}: {$report->tieu_de}",
            'dia_chi_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'du_lieu_cu' => json_encode([
                'id' => $report->id,
                'tieu_de' => $report->tieu_de,
                'trang_thai' => $report->trang_thai,
            ]),
            'du_lieu_moi' => null,
        ]);

        $report->delete();

        return redirect()->route('admin.reports.index')->with('success', 'Xóa phản ánh thành công!');
    }

    /**
     * Export reports to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'trang_thai',
            'danh_muc_id',
            'uu_tien_id',
            'co_quan_phu_trach_id',
            'tu_ngay',
            'den_ngay',
            'search'
        ]);

        $filename = 'phan-anh-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ReportsExport($filters),
            $filename
        );
    }
}
