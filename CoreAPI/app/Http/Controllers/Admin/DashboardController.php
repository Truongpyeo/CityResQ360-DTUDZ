<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhanAnh;
use App\Models\NguoiDung;
use App\Models\CoQuanXuLy;
use App\Models\QuanTriVien;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Get admin from DB to have fresh instance with relationships
        $authAdmin = auth()->guard('admin')->user();
        $admin = QuanTriVien::with('vaiTro')->find($authAdmin->id);

        // Get statistics
        $stats = [
            'total_reports' => PhanAnh::count(),
            'pending_reports' => PhanAnh::where('trang_thai', PhanAnh::TRANG_THAI_PENDING)->count(),
            'in_progress_reports' => PhanAnh::where('trang_thai', PhanAnh::TRANG_THAI_IN_PROGRESS)->count(),
            'resolved_reports' => PhanAnh::where('trang_thai', PhanAnh::TRANG_THAI_RESOLVED)->count(),
            'total_users' => NguoiDung::count(),
            'verified_users' => NguoiDung::where('xac_thuc_cong_dan', true)->count(),
            'total_agencies' => CoQuanXuLy::count(),
            'active_agencies' => CoQuanXuLy::where('trang_thai', CoQuanXuLy::TRANG_THAI_ACTIVE)->count(),
        ];

        // Get reports by category
        $reportsByCategory = PhanAnh::select('danh_muc_id', DB::raw('count(*) as total'))
            ->with('danhMuc')
            ->groupBy('danh_muc_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->danhMuc?->ten_danh_muc ?? 'Không xác định',
                    'total' => $item->total,
                ];
            });

        // Get reports by status
        $reportsByStatus = PhanAnh::select('trang_thai', DB::raw('count(*) as total'))
            ->groupBy('trang_thai')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $this->getStatusName($item->trang_thai),
                    'total' => $item->total,
                ];
            });

        // Get recent reports
        $recentReports = PhanAnh::with(['nguoiDung', 'coQuanXuLy', 'danhMuc', 'uuTien'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'tieu_de' => $report->tieu_de,
                    'danh_muc' => $report->danhMuc?->ten_danh_muc ?? 'Không xác định',
                    'uu_tien' => $report->uuTien?->ten_muc ?? 'Không xác định',
                    'trang_thai' => $report->getStatusName(),
                    'nguoi_dung' => $report->nguoiDung?->ho_ten,
                    'co_quan' => $report->coQuanXuLy?->ten_co_quan,
                    'created_at' => $report->created_at->format('d/m/Y H:i'),
                ];
            });

        // Get reports trend (last 7 days)
        $reportsTrend = PhanAnh::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as total')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => \Carbon\Carbon::parse($item->date)->format('d/m'),
                    'total' => $item->total,
                ];
            });

        return Inertia::render('admin/Dashboard', [
            'admin' => [
                'id' => $admin->id,
                'ten_quan_tri' => $admin->ten_quan_tri,
                'email' => $admin->email,
                'is_master' => $admin->is_master,
                'vai_tro' => $admin->vaiTro ? [
                    'id' => $admin->vaiTro->id,
                    'ten_vai_tro' => $admin->vaiTro->ten_vai_tro,
                    'slug' => $admin->vaiTro->slug,
                ] : null,
                'vai_tro_text' => $admin->getRoleName(),
                'anh_dai_dien' => $admin->anh_dai_dien,
                'permissions' => $admin->getPermissions(),
            ],
            'stats' => $stats,
            'reportsByCategory' => $reportsByCategory,
            'reportsByStatus' => $reportsByStatus,
            'recentReports' => $recentReports,
            'reportsTrend' => $reportsTrend,
        ]);
    }

    private function getStatusName($status): string
    {
        return match($status) {
            PhanAnh::TRANG_THAI_PENDING => 'Chờ xử lý',
            PhanAnh::TRANG_THAI_VERIFIED => 'Đã xác minh',
            PhanAnh::TRANG_THAI_IN_PROGRESS => 'Đang xử lý',
            PhanAnh::TRANG_THAI_RESOLVED => 'Đã giải quyết',
            PhanAnh::TRANG_THAI_REJECTED => 'Từ chối',
            default => 'Không xác định',
        };
    }
}
