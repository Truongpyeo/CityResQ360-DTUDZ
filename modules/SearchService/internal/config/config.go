package config

import (
	"os"
)

type Config struct {
	Port string
	Env  string

	// Meilisearch
	MeiliURL string
	MeiliKey string

	// MySQL
	MySQLHost     string
	MySQLPort     string
	MySQLUser     string
	MySQLPassword string
	MySQLDatabase string
}

func Load() *Config {
	return &Config{
		Port: getEnv("PORT", "8007"),
		Env:  getEnv("ENV", "development"),

		MeiliURL: getEnv("MEILI_URL", "http://localhost:7700"),
		MeiliKey: getEnv("MEILI_MASTER_KEY", ""),

		MySQLHost:     getEnv("MYSQL_HOST", "localhost"),
		MySQLPort:     getEnv("MYSQL_PORT", "3306"),
		MySQLUser:     getEnv("MYSQL_USER", "cityresq"),
		MySQLPassword: getEnv("MYSQL_PASSWORD", "cityresq_password"),
		MySQLDatabase: getEnv("MYSQL_DATABASE", "cityresq_db"),
	}
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}
