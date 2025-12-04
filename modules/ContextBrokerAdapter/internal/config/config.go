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
