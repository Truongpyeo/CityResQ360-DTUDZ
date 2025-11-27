<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * WeatherObservation Model
 * 
 * Stores weather data from OpenWeatherMap API
 */
class WeatherObservation extends Model
{
    protected $fillable = [
        'location_lat',
        'location_lng',
        'location_name',
        'temperature',
        'feels_like',
        'temp_min',
        'temp_max',
        'pressure',
        'humidity',
        'visibility',
        'wind_speed',
        'wind_direction',
        'wind_gust',
        'precipitation',
        'cloudiness',
        'weather_type',
        'weather_description',
        'weather_icon',
        'observed_at',
        'sunrise',
        'sunset',
        'source',
        'raw_data',
    ];

    protected $casts = [
        'location_lat' => 'float',
        'location_lng' => 'float',
        'temperature' => 'float',
        'feels_like' => 'float',
        'temp_min' => 'float',
        'temp_max' => 'float',
        'pressure' => 'float',
        'humidity' => 'float',
        'visibility' => 'float',
        'wind_speed' => 'float',
        'wind_direction' => 'integer',
        'wind_gust' => 'float',
        'precipitation' => 'float',
        'cloudiness' => 'integer',
        'observed_at' => 'datetime',
        'sunrise' => 'datetime',
        'sunset' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Get latest weather for a location
     */
    public static function getLatest($lat, $lon, $radiusKm = 5)
    {
        return static::whereRaw("
            (6371 * acos(
                cos(radians(?)) * cos(radians(location_lat)) * 
                cos(radians(location_lng) - radians(?)) + 
                sin(radians(?)) * sin(radians(location_lat))
            )) <= ?
        ", [$lat, $lon, $lat, $radiusKm])
        ->orderBy('observed_at', 'desc')
        ->first();
    }

    /**
     * Check if weather is risky
     */
    public function isRisky()
    {
        // Heavy rain
        if ($this->precipitation > 50) {
            return ['type' => 'flood', 'severity' => 'high'];
        }
        
        // Fire risk (high wind + low humidity)
        if ($this->wind_speed > 30 && $this->humidity < 30) {
            return ['type' => 'fire', 'severity' => 'medium'];
        }
        
        // Storm
        if ($this->weather_type === 'stormy' || $this->wind_speed > 50) {
            return ['type' => 'storm', 'severity' => 'high'];
        }
        
        return null;
    }
}
