<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * WeatherObserved NGSI-LD Resource
 * 
 * Transforms weather data to NGSI-LD WeatherObserved entity
 * following FiWARE Smart Data Model
 * 
 * @see https://github.com/smart-data-models/dataModel.Weather/blob/master/WeatherObserved/doc/spec.md
 */
class WeatherObservedResource extends JsonResource
{
    /**
     * Transform weather data to NGSI-LD format
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Generate URN format ID
        $entityId = "urn:ngsi-ld:WeatherObserved:" . 
                    "HCM-" . $this->observed_at->format('Y-m-d-H');
        
        // Get base URL for @context
        $baseUrl = config('app.url');
        
        return [
            'id' => $entityId,
            'type' => 'WeatherObserved',
            '@context' => $baseUrl . '/@context.jsonld',
            
            // Location as GeoProperty
            'location' => $this->geoProperty($this->location_lng, $this->location_lat),
            
            // Address
            'address' => $this->propertyValue([
                'addressLocality' => 'Ho Chi Minh City',
                'addressCountry' => 'VN'
            ]),
            
            // Temperature (Celsius)
            'temperature' => $this->propertyValue($this->temperature, 'CEL'),
            
            // Relative Humidity (0-1)
            'relativeHumidity' => $this->propertyValue($this->humidity / 100, 'P1'),
            
            // Precipitation (mm)
            'precipitation' => $this->when($this->precipitation !== null, function() {
                return $this->propertyValue($this->precipitation, 'MMT');
            }),
            
            // Wind Speed (km/h)
            'windSpeed' => $this->propertyValue($this->wind_speed, 'KMH'),
            
            // Atmospheric Pressure (hPa)
            'atmosphericPressure' => $this->when($this->pressure !== null, function() {
                return $this->propertyValue($this->pressure, 'HPA');
            }),
            
            // Weather Type
            'weatherType' => $this->propertyValue($this->weather_type ?? 'unknown'),
            
            // Date Observed
            'dateObserved' => $this->dateTimeProperty($this->observed_at),
            
            // Source
            'source' => $this->propertyValue($this->source ?? 'OpenWeatherMap'),
            
            // Data Provider
            'dataProvider' => $this->propertyValue('CityResQ360'),
        ];
    }
    
    /**
     * Create NGSI-LD Property with unit code
     * 
     * @param mixed $value
     * @param string $unitCode
     * @return array
     */
    protected function propertyValue($value, $unitCode = null)
    {
        $property = [
            'type' => 'Property',
            'value' => $value,
        ];
        
        if ($unitCode) {
            $property['unitCode'] = $unitCode;
        }
        
        return $property;
    }
    
    /**
     * Create NGSI-LD GeoProperty
     * 
     * @param float $longitude
     * @param float $latitude
     * @return array
     */
    protected function geoProperty($longitude, $latitude)
    {
        return [
            'type' => 'GeoProperty',
            'value' => [
                'type' => 'Point',
                'coordinates' => [(float)$longitude, (float)$latitude]
            ]
        ];
    }
    
    /**
     * Create NGSI-LD DateTime Property
     * 
     * @param \Carbon\Carbon $datetime
     * @return array
     */
    protected function dateTimeProperty($datetime)
    {
        return [
            'type' => 'Property',
            'value' => [
                '@type' => 'DateTime',
                '@value' => $datetime->toIso8601String()
            ]
        ];
    }
}
