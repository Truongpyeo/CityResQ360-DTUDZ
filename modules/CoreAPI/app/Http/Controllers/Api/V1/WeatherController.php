<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

class WeatherController extends BaseController
{
    /**
     * Get current weather data
     * GET /api/v1/weather/current
     */
    public function current(Request $request)
    {
        // Mock current weather data for Ho Chi Minh City
        $weatherData = [
            'location' => [
                'city' => 'Hồ Chí Minh',
                'lat' => 10.8231,
                'lon' => 106.6297,
            ],
            'current' => [
                'temp' => 32,
                'feels_like' => 38,
                'humidity' => 75,
                'pressure' => 1010,
                'wind_speed' => 15,
                'wind_direction' => 'SE',
                'description' => 'Nắng, có mây',
                'icon' => 'partly_cloudy',
                'uv_index' => 8,
                'visibility' => 10,
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        return $this->success($weatherData, 'Lấy dữ liệu thời tiết hiện tại thành công');
    }

    /**
     * Get weather forecast (next 7 days)
     * GET /api/v1/weather/forecast
     */
    public function forecast(Request $request)
    {
        $days = $request->input('days', 7);
        
        $forecast = [];
        for ($i = 0; $i < $days; $i++) {
            $date = now()->addDays($i);
            $forecast[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->locale('vi')->dayName,
                'temp_max' => rand(30, 35),
                'temp_min' => rand(24, 28),
                'humidity' => rand(70, 85),
                'rain_chance' => rand(20, 60),
                'description' => $i % 2 == 0 ? 'Nắng, có mây' : 'Mưa rào, có dông',
                'icon' => $i % 2 == 0 ? 'partly_cloudy' : 'rain',
            ];
        }

        return $this->success([
            'location' => 'Hồ Chí Minh',
            'forecast' => $forecast,
        ], 'Lấy dự báo thời tiết thành công');
    }

    /**
     * Get weather history
     * GET /api/v1/weather/history?days=7
     */
    public function history(Request $request)
    {
        $days = $request->input('days', 7);
        
        $history = [];
        for ($i = $days; $i > 0; $i--) {
            $date = now()->subDays($i);
            $history[] = [
                'date' => $date->format('Y-m-d'),
                'temp_avg' => rand(28, 32),
                'temp_max' => rand(32, 35),
                'temp_min' => rand(24, 27),
                'humidity_avg' => rand(70, 80),
                'rainfall' => rand(0, 50) / 10,
                'description' => rand(0, 1) ? 'Nắng' : 'Mưa',
            ];
        }

        return $this->success([
            'location' => 'Hồ Chí Minh',
            'period' => [
                'from' => now()->subDays($days)->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
            'history' => $history,
        ], 'Lấy lịch sử thời tiết thành công');
    }

    /**
     * Sync weather data (admin only)
     * POST /api/v1/weather/sync
     */
    public function sync(Request $request)
    {
        // Mock sync operation
        return $this->success([
            'synced' => true,
            'records_updated' => rand(50, 100),
            'last_sync' => now()->toIso8601String(),
        ], 'Đồng bộ dữ liệu thời tiết thành công');
    }
}
