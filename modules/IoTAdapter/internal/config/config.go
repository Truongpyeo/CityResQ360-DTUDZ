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

package config

import "os"

type Config struct {
	Port string
	
	// MQTT
	MQTTBroker   string
	MQTTPort     string
	MQTTClientID string
	
	// Orion-LD
	OrionURL string
	
	// Thresholds for alerts
	HeavyRainThreshold    float64 // mm/h
	UnhealthyAQIThreshold int     // AQI
	StrongWindThreshold   float64 // km/h
}

func Load() *Config {
	return &Config{
		Port: getEnv("PORT", "8011"),
		
		MQTTBroker:   getEnv("MQTT_BROKER", "mqtt"),
		MQTTPort:     getEnv("MQTT_PORT", "1883"),
		MQTTClientID: getEnv("MQTT_CLIENT_ID", "iot-adapter"),
		
		OrionURL: getEnv("ORION_URL", "http://orion-ld:1026"),
		
		HeavyRainThreshold:    50.0,
		UnhealthyAQIThreshold: 150,
		StrongWindThreshold:   60.0,
	}
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}
