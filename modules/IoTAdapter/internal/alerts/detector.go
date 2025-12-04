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

package alerts

import (
	"fmt"
	"log"
	
	"iotadapter/internal/config"
	"iotadapter/internal/models"
	"iotadapter/internal/orion"
	"iotadapter/internal/transform"
)

type Detector struct {
	cfg         *config.Config
	orionClient *orion.Client
	alertCount  int
}

func NewDetector(cfg *config.Config, orionClient *orion.Client) *Detector {
	return &Detector{
		cfg:         cfg,
		orionClient: orionClient,
		alertCount:  0,
	}
}

// CheckRainfall checks if rainfall exceeds threshold
func (d *Detector) CheckRainfall(data models.RainfallData) error {
	if data.Rainfall > d.cfg.HeavyRainThreshold {
		log.Printf("⚠️  Heavy rain detected: %.2f mm/h at %s (threshold: %.2f)",
			data.Rainfall, data.SensorName, d.cfg.HeavyRainThreshold)
		
		description := fmt.Sprintf("Heavy rainfall detected: %.2f mm/h at %s. Threshold: %.2f mm/h",
			data.Rainfall, data.SensorName, d.cfg.HeavyRainThreshold)
		
		alertEntity := transform.ToAlert(
			"heavyRain",
			"high",
			description,
			data.Location,
			data.SensorID,
		)
		
		if err := d.orionClient.CreateOrUpdateEntity(alertEntity); err != nil {
			return fmt.Errorf("failed to create rain alert: %w", err)
		}
		
		d.alertCount++
		log.Printf("🚨 Alert created for heavy rain at %s", data.SensorName)
	}
	
	return nil
}

// CheckAirQuality checks if AQI exceeds threshold
func (d *Detector) CheckAirQuality(data models.AirQualityData) error {
	if data.AQI > d.cfg.UnhealthyAQIThreshold {
		log.Printf("⚠️  Unhealthy air quality detected: AQI %d at %s (threshold: %d)",
			data.AQI, data.SensorName, d.cfg.UnhealthyAQIThreshold)
		
		description := fmt.Sprintf("Unhealthy air quality detected: AQI %d (%s) at %s. PM2.5: %.2f μg/m³",
			data.AQI, data.Category, data.SensorName, data.PM25)
		
		alertEntity := transform.ToAlert(
			"poorAirQuality",
			"high",
			description,
			data.Location,
			data.SensorID,
		)
		
		if err := d.orionClient.CreateOrUpdateEntity(alertEntity); err != nil {
			return fmt.Errorf("failed to create air quality alert: %w", err)
		}
		
		d.alertCount++
		log.Printf("🚨 Alert created for poor air quality at %s", data.SensorName)
	}
	
	return nil
}

// CheckWindSpeed checks if wind speed exceeds threshold
func (d *Detector) CheckWindSpeed(data models.WindSpeedData) error {
	if data.WindSpeed > d.cfg.StrongWindThreshold {
		log.Printf("⚠️  Strong wind detected: %.2f km/h at %s (threshold: %.2f)",
			data.WindSpeed, data.SensorName, d.cfg.StrongWindThreshold)
		
		description := fmt.Sprintf("Strong wind detected: %.2f km/h (%s) at %s. Gusts: %.2f km/h",
			data.WindSpeed, data.WindDirectionName, data.SensorName, data.GustSpeed)
		
		alertEntity := transform.ToAlert(
			"strongWind",
			"medium",
			description,
			data.Location,
			data.SensorID,
		)
		
		if err := d.orionClient.CreateOrUpdateEntity(alertEntity); err != nil {
			return fmt.Errorf("failed to create wind alert: %w", err)
		}
		
		d.alertCount++
		log.Printf("🚨 Alert created for strong wind at %s", data.SensorName)
	}
	
	return nil
}

// GetAlertCount returns total alerts created
func (d *Detector) GetAlertCount() int {
	return d.alertCount
}
