package config

import "os"

type Config struct {
	Port string
	
	// MySQL
	MySQLHost     string
	MySQLPort     string
	MySQLUser     string
	MySQLPassword string
	MySQLDatabase string
	
	// Orion-LD
	OrionURL string
}

func Load() *Config {
	return &Config{
		Port: getEnv("PORT", "8012"),
		
		MySQLHost:     getEnv("MYSQL_HOST", "mysql"),
		MySQLPort:     getEnv("MYSQL_PORT", "3306"),
		MySQLUser:     getEnv("MYSQL_USER", "cityresq"),
		MySQLPassword: getEnv("MYSQL_PASSWORD", "cityresq_password"),
		MySQLDatabase: getEnv("MYSQL_DATABASE", "cityresq_db"),
		
		OrionURL: getEnv("ORION_URL", "http://orion-ld:1026"),
	}
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}
