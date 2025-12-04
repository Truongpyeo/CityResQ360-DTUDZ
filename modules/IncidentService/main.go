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

package main

import (
	"log"
	"net/http"
	"os"

	"github.com/gin-gonic/gin"
)

func main() {
	r := gin.Default()

	r.GET("/", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{
			"service": "IncidentService",
			"status":  "running",
			"version": "0.1.0",
			"message": "Advanced incident management and dispatch service",
		})
	})

	r.GET("/health", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{
			"status": "healthy",
		})
	})

	// TODO: Implement incident endpoints
	v1 := r.Group("/api/v1")
	{
		v1.GET("/incidents", func(c *gin.Context) {
			c.JSON(http.StatusOK, gin.H{
				"success": true,
				"data":    []string{},
			})
		})

		v1.POST("/incidents", func(c *gin.Context) {
			c.JSON(http.StatusCreated, gin.H{
				"success": true,
				"message": "Endpoint not yet implemented",
			})
		})

		v1.POST("/dispatch", func(c *gin.Context) {
			c.JSON(http.StatusOK, gin.H{
				"success": true,
				"message": "Auto-dispatch not yet implemented",
			})
		})
	}

	port := os.Getenv("PORT")
	if port == "" {
		port = "8005"
	}

	log.Printf("IncidentService starting on port %s", port)
	r.Run(":" + port)
}
