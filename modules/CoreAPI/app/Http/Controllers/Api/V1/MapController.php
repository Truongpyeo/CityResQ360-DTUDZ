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
use App\Models\PhanAnh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Map Controller
 * 
 * Provides map-related endpoints for mobile app
 * - Display reports on map
 * - Heatmap data
 * - Cluster markers
 * - GTFS routes (public transport)
 */
class MapController extends BaseController
{
    /**
     * Get reports for map display
     * 
     * GET /api/v1/map/reports
     * Query: ?bounds=min_lat,min_lon,max_lat,max_lon&danh_muc=0,1,4&trang_thai=0,1,2
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reports(Request $request)
    {
        $query = PhanAnh::query()
            ->with(['nguoiDung:id,ho_ten,anh_dai_dien', 'danhMuc:id,ten_danh_muc'])
            ->where('la_cong_khai', true)
            ->whereNotNull('vi_do')
            ->whereNotNull('kinh_do');

        // Filter by bounds (viewport)
        if ($request->has('bounds')) {
            $bounds = explode(',', $request->get('bounds'));
            if (count($bounds) === 4) {
                [$minLat, $minLon, $maxLat, $maxLon] = $bounds;
                $query->whereBetween('vi_do', [$minLat, $maxLat])
                      ->whereBetween('kinh_do', [$minLon, $maxLon]);
            }
        }

        // Filter by category
        if ($request->has('danh_muc')) {
            $categories = is_array($request->danh_muc) 
                ? $request->danh_muc 
                : explode(',', $request->danh_muc);
            $query->whereIn('danh_muc_id', $categories);
        }

        // Filter by status
        if ($request->has('trang_thai')) {
            $statuses = is_array($request->trang_thai)
                ? $request->trang_thai
                : explode(',', $request->trang_thai);
            $query->whereIn('trang_thai', $statuses);
        }

        // Limit results for performance
        $reports = $query->limit(500)->get();

        // Transform to lightweight format for map
        $mapData = $reports->map(function ($report) {
            return [
                'id' => $report->id,
                'vi_do' => (float) $report->vi_do,
                'kinh_do' => (float) $report->kinh_do,
                'tieu_de' => $report->tieu_de,
                'danh_muc' => $report->danh_muc_id,
                'danh_muc_text' => $report->danhMuc->ten_danh_muc ?? 'Khác',
                'uu_tien' => $report->muc_uu_tien_id,
                'trang_thai' => $report->trang_thai,
                'marker_color' => $this->getMarkerColor($report->muc_uu_tien_id),
            ];
        });

        return $this->success($mapData, 'Lấy dữ liệu bản đồ thành công');
    }

    /**
     * Get heatmap data
     * 
     * GET /api/v1/map/heatmap
     * Query: ?bounds=...&danh_muc=0,1,4&tu_ngay=2025-11-01&den_ngay=2025-11-30
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function heatmap(Request $request)
    {
        $query = PhanAnh::query()
            ->select('vi_do', 'kinh_do', DB::raw('COUNT(*) as weight'))
            ->where('la_cong_khai', true)
            ->whereNotNull('vi_do')
            ->whereNotNull('kinh_do')
            ->groupBy('vi_do', 'kinh_do');

        // Filter by bounds
        if ($request->has('bounds')) {
            $bounds = explode(',', $request->get('bounds'));
            if (count($bounds) === 4) {
                [$minLat, $minLon, $maxLat, $maxLon] = $bounds;
                $query->whereBetween('vi_do', [$minLat, $maxLat])
                      ->whereBetween('kinh_do', [$minLon, $maxLon]);
            }
        }

        // Filter by category
        if ($request->has('danh_muc')) {
            $categories = is_array($request->danh_muc)
                ? $request->danh_muc
                : explode(',', $request->danh_muc);
            $query->whereIn('danh_muc_id', $categories);
        }

        // Filter by date range
        if ($request->has('tu_ngay')) {
            $query->where('created_at', '>=', $request->tu_ngay);
        }
        if ($request->has('den_ngay')) {
            $query->where('created_at', '<=', $request->den_ngay . ' 23:59:59');
        }

        $heatmapData = $query->get()->map(function ($point) {
            return [
                'vi_do' => (float) $point->vi_do,
                'kinh_do' => (float) $point->kinh_do,
                'weight' => (int) $point->weight,
            ];
        });

        return $this->success($heatmapData, 'Lấy dữ liệu heatmap thành công');
    }

    /**
     * Get cluster markers
     * 
     * GET /api/v1/map/clusters
     * Query: ?zoom=12&bounds=...
     * 
     * Groups nearby reports into clusters based on zoom level
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clusters(Request $request)
    {
        $zoom = $request->get('zoom', 12);
        
        // Calculate precision for clustering based on zoom level
        // Higher zoom = more precision = smaller clusters
        $latPrecision = $this->getLatPrecision($zoom);
        $lonPrecision = $this->getLonPrecision($zoom);

        $query = PhanAnh::query()
            ->select(
                DB::raw("ROUND(vi_do / $latPrecision) * $latPrecision as cluster_lat"),
                DB::raw("ROUND(kinh_do / $lonPrecision) * $lonPrecision as cluster_lon"),
                DB::raw('COUNT(*) as count'),
                DB::raw('MIN(id) as sample_id')
            )
            ->where('la_cong_khai', true)
            ->whereNotNull('vi_do')
            ->whereNotNull('kinh_do')
            ->groupBy('cluster_lat', 'cluster_lon');

        // Filter by bounds
        if ($request->has('bounds')) {
            $bounds = explode(',', $request->get('bounds'));
            if (count($bounds) === 4) {
                [$minLat, $minLon, $maxLat, $maxLon] = $bounds;
                $query->whereBetween('vi_do', [$minLat, $maxLat])
                      ->whereBetween('kinh_do', [$minLon, $maxLon]);
            }
        }

        $clusters = $query->get()->map(function ($cluster) {
            return [
                'vi_do' => (float) $cluster->cluster_lat,
                'kinh_do' => (float) $cluster->cluster_lon,
                'count' => (int) $cluster->count,
                'sample_id' => $cluster->sample_id,
            ];
        });

        return $this->success($clusters, 'Lấy cluster markers thành công');
    }

    /**
     * Get GTFS routes (public transport)
     * 
     * GET /api/v1/map/routes
     * Query: ?vi_do=10.8231&kinh_do=106.6297&radius=2
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gtfsRoutes(Request $request)
    {
        // TODO: Implement when GTFS data is available
        // For now, return empty array
        
        $lat = $request->get('vi_do');
        $lon = $request->get('kinh_do');
        $radius = $request->get('radius', 2); // km

        // Mock data structure for future implementation
        $routes = [
            // Example:
            // [
            //     'id' => 1,
            //     'ten_tuyen' => 'Tuyến xe buýt số 1',
            //     'loai' => 'bus',
            //     'diem_dung' => [
            //         [
            //             'id' => 1,
            //             'ten_diem' => 'Bến xe buýt Bến Thành',
            //             'vi_do' => 10.8231,
            //             'kinh_do' => 106.6297,
            //             'khoang_cach' => 0.5 // km
            //         ]
            //     ]
            // ]
        ];

        return $this->success($routes, 'GTFS routes (coming soon)');
    }

    /**
     * Get marker color based on priority
     * 
     * @param int|null $priority
     * @return string
     */
    private function getMarkerColor($priority)
    {
        $colors = [
            1 => '#4CAF50', // Low - Green
            2 => '#FFC107', // Medium - Yellow
            3 => '#FF9800', // High - Orange
            4 => '#F44336', // Critical - Red
        ];

        return $colors[$priority] ?? '#9E9E9E'; // Default - Gray
    }

    /**
     * Get latitude precision for clustering
     * 
     * @param int $zoom
     * @return float
     */
    private function getLatPrecision($zoom)
    {
        // Zoom levels:
        // 10-11: 0.1 degree (~11km)
        // 12-13: 0.01 degree (~1.1km)
        // 14-15: 0.001 degree (~110m)
        // 16+: 0.0001 degree (~11m)
        
        $precisions = [
            10 => 0.1,
            11 => 0.1,
            12 => 0.01,
            13 => 0.01,
            14 => 0.001,
            15 => 0.001,
            16 => 0.0001,
        ];

        return $precisions[$zoom] ?? 0.01;
    }

    /**
     * Get longitude precision for clustering
     * 
     * @param int $zoom
     * @return float
     */
    private function getLonPrecision($zoom)
    {
        // Same as latitude for simplicity
        return $this->getLatPrecision($zoom);
    }
}
