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
