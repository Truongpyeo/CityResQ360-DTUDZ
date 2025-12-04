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

package models

// SensorData represents generic sensor data from MQTT
type SensorData struct {
	SensorID   string                 `json:"sensorId"`
	SensorName string                 `json:"sensorName"`
	Location   Location               `json:"location"`
	Timestamp  string                 `json:"timestamp"`
	Data       map[string]interface{} `json:"-"` // Additional fields
}

// Location represents sensor coordinates
type Location struct {
	Lat float64 `json:"lat"`
	Lng float64 `json:"lng"`
}

// RainfallData represents rainfall sensor data
type RainfallData struct {
	SensorID   string   `json:"sensorId"`
	SensorName string   `json:"sensorName"`
	Location   Location `json:"location"`
	Rainfall   float64  `json:"rainfall"`
	Unit       string   `json:"unit"`
	Status     string   `json:"status"`
	Timestamp  string   `json:"timestamp"`
}

// AirQualityData represents air quality sensor data
type AirQualityData struct {
	SensorID   string   `json:"sensorId"`
	SensorName string   `json:"sensorName"`
	Location   Location `json:"location"`
	PM25       float64  `json:"pm25"`
	PM10       float64  `json:"pm10"`
	AQI        int      `json:"aqi"`
	Category   string   `json:"category"`
	Timestamp  string   `json:"timestamp"`
}

// WindSpeedData represents wind speed sensor data
type WindSpeedData struct {
	SensorID          string   `json:"sensorId"`
	SensorName        string   `json:"sensorName"`
	Location          Location `json:"location"`
	WindSpeed         float64  `json:"windSpeed"`
	WindDirection     int      `json:"windDirection"`
	WindDirectionName string   `json:"windDirectionName"`
	GustSpeed         float64  `json:"gustSpeed"`
	Unit              string   `json:"unit"`
	Category          string   `json:"category"`
	Timestamp         string   `json:"timestamp"`
}

// WeatherObserved represents aggregated weather data for a location
type WeatherObserved struct {
	SensorID      string
	Location      Location
	Rainfall      *float64
	AQI           *int
	PM25          *float64
	PM10          *float64
	WindSpeed     *float64
	WindDirection *int
	GustSpeed     *float64
	Timestamp     string
}

// NGSILDEntity represents an NGSI-LD entity
type NGSILDEntity map[string]interface{}

// SensorStats represents statistics for monitoring
type SensorStats struct {
	TotalMessages       int `json:"totalMessages"`
	RainfallMessages    int `json:"rainfallMessages"`
	AirQualityMessages  int `json:"airQualityMessages"`
	WindSpeedMessages   int `json:"windSpeedMessages"`
	EntitiesCreated     int `json:"entitiesCreated"`
	AlertsCreated       int `json:"alertsCreated"`
}
