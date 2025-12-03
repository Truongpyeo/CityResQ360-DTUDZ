package transform

import (
	"fmt"
	"time"
	
	"iotadapter/internal/models"
)

// ToWeatherObserved converts sensor data to NGSI-LD WeatherObserved entity
func ToWeatherObserved(sensorID string, location models.Location, data models.WeatherObserved) models.NGSILDEntity {
	// Generate unique ID based on sensor and time
	timestamp := time.Now().Unix()
	entityID := fmt.Sprintf("urn:ngsi-ld:WeatherObserved:%s:%d", sensorID, timestamp)
	
	entity := models.NGSILDEntity{
		"id":       entityID,
		"type":     "WeatherObserved",
		"@context": "https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld",
	}
	
	// Location (GeoProperty)
	entity["location"] = map[string]interface{}{
		"type": "GeoProperty",
		"value": map[string]interface{}{
			"type":        "Point",
			"coordinates": []float64{location.Lng, location.Lat},
		},
	}
	
	// Rainfall
	if data.Rainfall != nil {
		entity["precipitation"] = map[string]interface{}{
			"type":     "Property",
			"value":    *data.Rainfall,
			"unitCode": "MMH", // mm/h
		}
	}
	
	// Air Quality Index
	if data.AQI != nil {
		entity["airQualityIndex"] = map[string]interface{}{
			"type":  "Property",
			"value": *data.AQI,
		}
	}
	
	// PM2.5
	if data.PM25 != nil {
		entity["pm25"] = map[string]interface{}{
			"type":     "Property",
			"value":    *data.PM25,
			"unitCode": "GP", // μg/m³
		}
	}
	
	// PM10
	if data.PM10 != nil {
		entity["pm10"] = map[string]interface{}{
			"type":     "Property",
			"value":    *data.PM10,
			"unitCode": "GP",
		}
	}
	
	// Wind Speed
	if data.WindSpeed != nil {
		entity["windSpeed"] = map[string]interface{}{
			"type":     "Property",
			"value":    *data.WindSpeed,
			"unitCode": "KMH",
		}
	}
	
	// Wind Direction
	if data.WindDirection != nil {
		entity["windDirection"] = map[string]interface{}{
			"type":  "Property",
			"value": *data.WindDirection,
		}
	}
	
	// Sensor source
	entity["dataProvider"] = map[string]interface{}{
		"type":  "Property",
		"value": "CityResQ360-IoT",
	}
	
	entity["source"] = map[string]interface{}{
		"type":  "Property",
		"value": sensorID,
	}
	
	return entity
}

// ToAlert converts threshold violation to NGSI-LD Alert entity
func ToAlert(alertType string, severity string, description string, location models.Location, sensorID string) models.NGSILDEntity {
	timestamp := time.Now().Unix()
	entityID := fmt.Sprintf("urn:ngsi-ld:Alert:IoT:%s:%d", alertType, timestamp)
	
	entity := models.NGSILDEntity{
		"id":       entityID,
		"type":     "Alert",
		"@context": "https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld",
	}
	
	// Category
	entity["category"] = map[string]interface{}{
		"type":  "Property",
		"value": "weather",
	}
	
	// Sub-category (heavy rain, poor air quality, strong wind)
	entity["subCategory"] = map[string]interface{}{
		"type":  "Property",
		"value": alertType,
	}
	
	// Severity
	entity["severity"] = map[string]interface{}{
		"type":  "Property",
		"value": severity,
	}
	
	// Description
	entity["description"] = map[string]interface{}{
		"type":  "Property",
		"value": description,
	}
	
	// Location
	entity["location"] = map[string]interface{}{
		"type": "GeoProperty",
		"value": map[string]interface{}{
			"type":        "Point",
			"coordinates": []float64{location.Lng, location.Lat},
		},
	}
	
	// Alert source
	entity["alertSource"] = map[string]interface{}{
		"type":  "Property",
		"value": "iot-sensor",
	}
	
	entity["source"] = map[string]interface{}{
		"type":  "Property",
		"value": sensorID,
	}
	
	return entity
}
