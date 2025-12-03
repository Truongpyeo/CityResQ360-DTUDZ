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
