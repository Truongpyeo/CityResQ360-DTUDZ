<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhanAnh;
use App\Models\NguoiDung;
use App\Models\CoQuanXuLy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        $startDate = $request->input('tu_ngay', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('den_ngay', Carbon::now()->format('Y-m-d'));

        // Overview statistics
        $stats = [
            'total_reports' => PhanAnh::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_users' => NguoiDung::where('trang_thai', 1)->count(),
            'total_agencies' => CoQuanXuLy::where('trang_thai', 1)->count(),
            'resolved_reports' => PhanAnh::where('trang_thai', 3)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_reports' => PhanAnh::where('trang_thai', 0)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'in_progress_reports' => PhanAnh::where('trang_thai', 2)->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        // Calculate resolution rate
        $stats['resolution_rate'] = $stats['total_reports'] > 0
            ? round(($stats['resolved_reports'] / $stats['total_reports']) * 100, 2)
            : 0;

        // Daily trend data (last 30 days)
        $dailyTrends = PhanAnh::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN trang_thai = 3 THEN 1 ELSE 0 END) as resolved')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Reports by category
        $reportsByCategory = PhanAnh::join('danh_muc_phan_anhs', 'phan_anhs.danh_muc_id', '=', 'danh_muc_phan_anhs.id')
            ->select('danh_muc_phan_anhs.ten_danh_muc as name', DB::raw('COUNT(*) as count'))
            ->whereBetween('phan_anhs.created_at', [$startDate, $endDate])
            ->groupBy('danh_muc_phan_anhs.ten_danh_muc')
            ->orderBy('count', 'desc')
            ->get();

        // Reports by priority
        $reportsByPriority = PhanAnh::join('muc_uu_tiens', 'phan_anhs.uu_tien_id', '=', 'muc_uu_tiens.id')
            ->select('muc_uu_tiens.ten_muc as name', DB::raw('COUNT(*) as count'))
            ->whereBetween('phan_anhs.created_at', [$startDate, $endDate])
            ->groupBy('muc_uu_tiens.ten_muc')
            ->orderBy('count', 'desc')
            ->get();

        // Top agencies by performance
        $agencyPerformance = CoQuanXuLy::select(
                'co_quan_xu_lys.ten_co_quan',
                DB::raw('COUNT(phan_anhs.id) as total_reports'),
                DB::raw('SUM(CASE WHEN phan_anhs.trang_thai = 3 THEN 1 ELSE 0 END) as resolved_reports'),
                DB::raw('ROUND(SUM(CASE WHEN phan_anhs.trang_thai = 3 THEN 1 ELSE 0 END) * 100.0 / COUNT(phan_anhs.id), 2) as resolution_rate')
            )
            ->leftJoin('phan_anhs', function($join) use ($startDate, $endDate) {
                $join->on('co_quan_xu_lys.id', '=', 'phan_anhs.co_quan_phu_trach_id')
                     ->whereBetween('phan_anhs.created_at', [$startDate, $endDate]);
            })
            ->groupBy('co_quan_xu_lys.id', 'co_quan_xu_lys.ten_co_quan')
            ->having('total_reports', '>', 0)
            ->orderBy('resolution_rate', 'desc')
            ->limit(10)
            ->get();

        // Top users by reports
        $topUsers = NguoiDung::select(
                'nguoi_dungs.ho_ten',
                'nguoi_dungs.email',
                DB::raw('COUNT(phan_anhs.id) as total_reports'),
                DB::raw('SUM(CASE WHEN phan_anhs.trang_thai = 3 THEN 1 ELSE 0 END) as resolved_reports')
            )
            ->leftJoin('phan_anhs', function($join) use ($startDate, $endDate) {
                $join->on('nguoi_dungs.id', '=', 'phan_anhs.nguoi_dung_id')
                     ->whereBetween('phan_anhs.created_at', [$startDate, $endDate]);
            })
            ->groupBy('nguoi_dungs.id', 'nguoi_dungs.ho_ten', 'nguoi_dungs.email')
            ->having('total_reports', '>', 0)
            ->orderBy('total_reports', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('admin/Analytics/Index', [
            'stats' => $stats,
            'dailyTrends' => $dailyTrends,
            'reportsByCategory' => $reportsByCategory,
            'reportsByPriority' => $reportsByPriority,
            'agencyPerformance' => $agencyPerformance,
            'topUsers' => $topUsers,
            'filters' => [
                'tu_ngay' => $startDate,
                'den_ngay' => $endDate,
            ],
        ]);
    }

    /**
     * Get comparison data between two time periods
     */
    public function comparison(Request $request)
    {
        $currentStart = $request->input('current_start', Carbon::now()->subDays(30)->format('Y-m-d'));
        $currentEnd = $request->input('current_end', Carbon::now()->format('Y-m-d'));

        $days = Carbon::parse($currentStart)->diffInDays(Carbon::parse($currentEnd));
        $previousStart = Carbon::parse($currentStart)->subDays($days)->format('Y-m-d');
        $previousEnd = Carbon::parse($currentStart)->subDay()->format('Y-m-d');

        // Current period stats
        $currentStats = [
            'total_reports' => PhanAnh::whereBetween('created_at', [$currentStart, $currentEnd])->count(),
            'resolved_reports' => PhanAnh::where('trang_thai', 3)->whereBetween('created_at', [$currentStart, $currentEnd])->count(),
            'avg_response_time' => PhanAnh::whereBetween('created_at', [$currentStart, $currentEnd])
                ->whereNotNull('updated_at')
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, updated_at)')),
        ];

        // Previous period stats
        $previousStats = [
            'total_reports' => PhanAnh::whereBetween('created_at', [$previousStart, $previousEnd])->count(),
            'resolved_reports' => PhanAnh::where('trang_thai', 3)->whereBetween('created_at', [$previousStart, $previousEnd])->count(),
            'avg_response_time' => PhanAnh::whereBetween('created_at', [$previousStart, $previousEnd])
                ->whereNotNull('updated_at')
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, updated_at)')),
        ];

        // Calculate changes
        $comparison = [
            'total_reports_change' => $previousStats['total_reports'] > 0
                ? round((($currentStats['total_reports'] - $previousStats['total_reports']) / $previousStats['total_reports']) * 100, 2)
                : 0,
            'resolved_reports_change' => $previousStats['resolved_reports'] > 0
                ? round((($currentStats['resolved_reports'] - $previousStats['resolved_reports']) / $previousStats['resolved_reports']) * 100, 2)
                : 0,
            'response_time_change' => $previousStats['avg_response_time'] > 0
                ? round((($currentStats['avg_response_time'] - $previousStats['avg_response_time']) / $previousStats['avg_response_time']) * 100, 2)
                : 0,
        ];

        return response()->json([
            'current' => $currentStats,
            'previous' => $previousStats,
            'comparison' => $comparison,
        ]);
    }
}
