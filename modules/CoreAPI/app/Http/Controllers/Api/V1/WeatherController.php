<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\WeatherObservedResource;
use App\Models\WeatherObservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Weather API Controller
 * 
 * OpenWeatherMap integration for weather data
 */
class WeatherController extends BaseController
{
    /**
     * Lấy thời tiết hiện tại
     * 
     * GET /api/v1/weather/current
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function current(Request $request)
    {
        $lat = $request->query('lat', 10.7769);
        $lon = $request->query('lon', 106.7009);
        
        try {
            // Check cache first (last 30 minutes)
            $cached = WeatherObservation::where('location_lat', $lat)
                ->where('location_lng', $lon)
                ->where('observed_at', '>=', now()->subMinutes(30))
                ->orderBy('observed_at', 'desc')
                ->first();
            
            if ($cached) {
                return $this->sendResponse($cached, 'Weather data from cache');
            }
            
            // Fetch from OpenWeatherMap
            $weather = $this->fetchWeatherFromAPI($lat, $lon);
            
            return $this->sendResponse($weather, 'Weather data fetched successfully');
            
        } catch (\Exception $e) {
            Log::error('Weather fetch error: ' . $e->getMessage());
            return $this->sendError('Failed to fetch weather data', $e->getMessage(), 500);
        }
    }
    
    /**
     * Lấy dự báo 5 ngày
     * 
     * GET /api/v1/weather/forecast
     */
    public function forecast(Request $request)
    {
        $lat = $request->query('lat', 10.7769);
        $lon = $request->query('lon', 106.7009);
        
        try {
            $apiKey = config('services.openweather.key');
            
            $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/forecast', [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $apiKey,
                'units' => 'metric',
                'lang' => 'vi'
            ]);
            
            if (!$response->successful()) {
                throw new \Exception('OpenWeatherMap API error');
            }
            
            $data = $response->json();
            
            // Transform forecast data
            $forecast = collect($data['list'])->map(function($item) use ($lat, $lon) {
                return $this->transformWeatherData($item, $lat, $lon, $item['dt_txt']);
            });
            
            return $this->sendResponse([
                'city' => $data['city']['name'] ?? 'Ho Chi Minh City',
                'country' => $data['city']['country'] ?? 'VN',
                'forecast' => $forecast
            ], 'Forecast data fetched successfully');            
        } catch (\Exception $e) {
            Log::error('Forecast fetch error: ' . $e->getMessage());
            return $this->sendError('Failed to fetch forecast', $e->getMessage(), 500);
        }
    }
    
    /**
     * Lấy lịch sử thời tiết
     * 
     * GET /api/v1/weather/history
     */
    public function history(Request $request)
    {
        $lat = $request->query('lat', 10.7769);
        $lon = $request->query('lon', 106.7009);
        $days = min($request->query('days', 7), 30);
        
        $history = WeatherObservation::where('location_lat', $lat)
            ->where('location_lng', $lon)
            ->where('observed_at', '>=', now()->subDays($days))
            ->orderBy('observed_at', 'desc')
            ->get();
        
        return $this->sendResponse($history, "Weather history for last $days days");
    }
    
    /**
     * Sync weather data (được gọi bởi cron job)
     * 
     * POST /api/v1/weather/sync
     */
    public function sync()
    {
        try {
            // TP.HCM center
            $weather = $this->fetchWeatherFromAPI(10.7769, 106.7009);
            
            // Check for weather risks
            $risks = $this->assessWeatherRisks($weather);
            
            if ($risks) {
                // TODO: Create proactive alerts
                Log::info('Weather risk detected:', $risks);
            }
            
            return $this->sendResponse($weather, 'Weather data synced');
            
        } catch (\Exception $e) {
            Log::error('Weather sync error: ' . $e->getMessage());
            return $this->sendError('Sync failed', $e->getMessage(), 500);
        }
    }
    
    /**
     * Fetch weather from OpenWeatherMap API
     * 
     * @param float $lat
     * @param float $lon
     * @return WeatherObservation
     */
    private function fetchWeatherFromAPI($lat, $lon)
    {
        $apiKey = config('services.openweather.key');
        
        $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $apiKey,
            'units' => 'metric',
            'lang' => 'vi'
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('OpenWeatherMap API returned error');
        }
        
        $data = $response->json();
        
        // Transform and store
        return $this->storeWeatherData($data, $lat, $lon);
    }
    
    /**
     * Transform and store weather data
     */
    private function storeWeatherData($data, $lat, $lon, $dateTime = null)
    {
        $observedAt = $dateTime ? \Carbon\Carbon::parse($dateTime) : now();
        
        $weatherData = $this->transformWeatherData($data, $lat, $lon, $dateTime);
        
        return WeatherObservation::create($weatherData);
    }
    
    /**
     * Transform OpenWeatherMap data to internal format
     */
    private function transformWeatherData($data, $lat, $lon, $dateTime = null)
    {
        $observedAt = $dateTime ?? date('Y-m-d H:i:s', $data['dt']);
        
        return [
            'location_lat' => $lat,
            'location_lng' => $lon,
            'location_name' => $data['name'] ?? 'Ho Chi Minh City',
            'temperature' => $data['main']['temp'] ?? null,
            'feels_like' => $data['main']['feels_like'] ?? null,
            'temp_min' => $data['main']['temp_min'] ?? null,
            'temp_max' => $data['main']['temp_max'] ?? null,
            'pressure' => $data['main']['pressure'] ?? null,
            'humidity' => $data['main']['humidity'] ?? null,
            'visibility' => isset($data['visibility']) ? $data['visibility'] / 1000 : null,
            'wind_speed' => isset($data['wind']['speed']) ? $data['wind']['speed'] * 3.6 : null,
            'wind_direction' => $data['wind']['deg'] ?? null,
            'wind_gust' => isset($data['wind']['gust']) ? $data['wind']['gust'] * 3.6 : null,
            'cloudiness' => $data['clouds']['all'] ?? null,
            'precipitation' => $this->calculatePrecipitation($data),
            'weather_type' => $this->mapWeatherType($data['weather'][0]['main'] ?? null),
            'weather_description' => $data['weather'][0]['description'] ?? null,
            'weather_icon' => $data['weather'][0]['icon'] ?? null,
            'observed_at' => $observedAt,
            'sunrise' => isset($data['sys']['sunrise']) ? date('Y-m-d H:i:s', $data['sys']['sunrise']) : null,
            'sunset' => isset($data['sys']['sunset']) ? date('Y-m-d H:i:s', $data['sys']['sunset']) : null,
            'source' => 'OpenWeatherMap',
            'raw_data' => $data
        ];
    }
    
    /**
     * Calculate precipitation
     */
    private function calculatePrecipitation($data)
    {
        $precipitation = 0;
        
        if (isset($data['rain'])) {
            $precipitation += $data['rain']['1h'] ?? $data['rain']['3h'] ?? 0;
        }
        
        if (isset($data['snow'])) {
            $precipitation += $data['snow']['1h'] ?? $data['snow']['3h'] ?? 0;
        }
        
        return $precipitation;
    }
    
    /**
     * Map weather type
     */
    private function mapWeatherType($weatherMain)
    {
        $typeMap = [
            'Clear' => 'clear',
            'Clouds' => 'cloudy',
            'Rain' => 'rainy',
            'Drizzle' => 'rainy',
            'Thunderstorm' => 'stormy',
            'Snow' => 'snowy',
            'Mist' => 'foggy',
            'Fog' => 'foggy',
        ];
        
        return $typeMap[$weatherMain] ?? 'unknown';
    }
    
    /**
     * Assess weather risks
     */
    private function assessWeatherRisks(WeatherObservation $weather)
    {
        return $weather->isRisky();
    }
}
