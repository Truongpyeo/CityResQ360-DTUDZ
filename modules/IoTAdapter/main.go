package main

import (
	"encoding/json"
	"log"
	"os"
	"os/signal"
	"syscall"
	
	"github.com/gin-gonic/gin"
	"iotadapter/internal/alerts"
	"iotadapter/internal/config"
	"iotadapter/internal/handlers"
	"iotadapter/internal/models"
	"iotadapter/internal/mqtt"
	"iotadapter/internal/orion"
	"iotadapter/internal/transform"
)

func main() {
	log.Println("🌟 Starting IoT Adapter...")
	
	// Load config
	cfg := config.Load()
	log.Printf("📋 Config loaded - MQTT: %s:%s, Orion: %s",
		cfg.MQTTBroker, cfg.MQTTPort, cfg.OrionURL)
	
	// Create Orion-LD client
	orionClient := orion.NewClient(cfg)
	log.Println("✅ Orion-LD client initialized")
	
	// Create alert detector
	detector := alerts.NewDetector(cfg, orionClient)
	log.Printf("🚨 Alert detector initialized (thresholds: rain>%.0fmm/h, AQI>%d, wind>%.0fkm/h)",
		cfg.HeavyRainThreshold, cfg.UnhealthyAQIThreshold, cfg.StrongWindThreshold)
	
	// Create handlers
	h := handlers.NewHandler()
	
	// Create MQTT client
	mqttClient, err := mqtt.NewClient(cfg)
	if err != nil {
		log.Fatalf("❌ Failed to create MQTT client: %v", err)
	}
	
	// Connect to MQTT broker
	if err := mqttClient.Connect(); err != nil {
		log.Fatalf("❌ Failed to connect to MQTT: %v", err)
	}
	defer mqttClient.Disconnect()
	
	// Subscribe to rainfall sensors
	mqttClient.Subscribe("cityresq/sensors/rainfall/+", func(topic string, payload []byte) error {
		var data models.RainfallData
		if err := json.Unmarshal(payload, &data); err != nil {
			return err
		}
		
		log.Printf("🌧️  %s: %.2f mm/h (%s)", data.SensorID, data.Rainfall, data.Status)
		
		// Create WeatherObserved entity
		weatherData := models.WeatherObserved{
			SensorID:  data.SensorID,
			Location:  data.Location,
			Rainfall:  &data.Rainfall,
			Timestamp: data.Timestamp,
		}
		
		entity := transform.ToWeatherObserved(data.SensorID, data.Location, weatherData)
		if err := orionClient.CreateOrUpdateEntity(entity); err != nil {
			log.Printf("⚠️  Failed to push rainfall entity: %v", err)
		} else {
			h.IncrementEntitiesCreated()
		}
		
		// Check threshold
		if err := detector.CheckRainfall(data); err != nil {
			log.Printf("⚠️  Failed to create rain alert: %v", err)
		} else if data.Rainfall > cfg.HeavyRainThreshold {
			h.IncrementAlertsCreated()
		}
		
		h.IncrementRainfall()
		return nil
	})
	
	// Subscribe to air quality sensors
	mqttClient.Subscribe("cityresq/sensors/airquality/+", func(topic string, payload []byte) error {
		var data models.AirQualityData
		if err := json.Unmarshal(payload, &data); err != nil {
			return err
		}
		
		log.Printf("🌫️  %s: AQI %d (%s)", data.SensorID, data.AQI, data.Category)
		
		// Create WeatherObserved entity
		weatherData := models.WeatherObserved{
			SensorID:  data.SensorID,
			Location:  data.Location,
			AQI:       &data.AQI,
			PM25:      &data.PM25,
			PM10:      &data.PM10,
			Timestamp: data.Timestamp,
		}
		
		entity := transform.ToWeatherObserved(data.SensorID, data.Location, weatherData)
		if err := orionClient.CreateOrUpdateEntity(entity); err != nil {
			log.Printf("⚠️  Failed to push air quality entity: %v", err)
		} else {
			h.IncrementEntitiesCreated()
		}
		
		// Check threshold
		if err := detector.CheckAirQuality(data); err != nil {
			log.Printf("⚠️  Failed to create AQI alert: %v", err)
		} else if data.AQI > cfg.UnhealthyAQIThreshold {
			h.IncrementAlertsCreated()
		}
		
		h.IncrementAirQuality()
		return nil
	})
	
	// Subscribe to wind speed sensors
	mqttClient.Subscribe("cityresq/sensors/windspeed/+", func(topic string, payload []byte) error {
		var data models.WindSpeedData
		if err := json.Unmarshal(payload, &data); err != nil {
			return err
		}
		
		log.Printf("💨 %s: %.2f km/h %s", data.SensorID, data.WindSpeed, data.WindDirectionName)
		
		// Create WeatherObserved entity
		weatherData := models.WeatherObserved{
			SensorID:      data.SensorID,
			Location:      data.Location,
			WindSpeed:     &data.WindSpeed,
			WindDirection: &data.WindDirection,
			GustSpeed:     &data.GustSpeed,
			Timestamp:     data.Timestamp,
		}
		
		entity := transform.ToWeatherObserved(data.SensorID, data.Location, weatherData)
		if err := orionClient.CreateOrUpdateEntity(entity); err != nil {
			log.Printf("⚠️  Failed to push wind entity: %v", err)
		} else {
			h.IncrementEntitiesCreated()
		}
		
		// Check threshold
		if err := detector.CheckWindSpeed(data); err != nil {
			log.Printf("⚠️  Failed to create wind alert: %v", err)
		} else if data.WindSpeed > cfg.StrongWindThreshold {
			h.IncrementAlertsCreated()
		}
		
		h.IncrementWindSpeed()
		return nil
	})
	
	log.Println("📡 Subscribed to all sensor topics")
	log.Println("🚀 IoT Adapter is running!")
	log.Println("")
	
	// Setup HTTP server for stats/health
	router := gin.Default()
	
	router.GET("/", func(c *gin.Context) {
		c.JSON(200, gin.H{
			"service":     "IoT Adapter",
			"version":     "1.0.0",
			"description": "MQTT → NGSI-LD → Orion Context Broker",
		})
	})
	
	router.GET("/health", h.Health)
	router.GET("/api/v1/stats", h.GetStats)
	
	// Start HTTP server in goroutine
	go func() {
		addr := ":" + cfg.Port
		log.Printf("🌐 HTTP server starting on %s...", addr)
		if err := router.Run(addr); err != nil {
			log.Printf("⚠️  HTTP server error: %v", err)
		}
	}()
	
	// Keep the main goroutine alive to process MQTT messages
	// This is critical - MQTT client needs an active event loop
	log.Println("⏳ Keeping MQTT client alive and processing messages...")
	
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	
	// Wait for shutdown signal
	<-quit
	
	log.Println("\n🛑 Shutting down IoT Adapter...")
	mqttClient.Disconnect()
	log.Println("✅ Shutdown complete")
}
