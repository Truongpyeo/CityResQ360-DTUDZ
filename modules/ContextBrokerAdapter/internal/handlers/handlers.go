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

package handlers

import (
	"log"
	"net/http"
	
	"github.com/gin-gonic/gin"
	"contextbrokeradapter/internal/mysql"
	"contextbrokeradapter/internal/orion"
	"contextbrokeradapter/internal/transform"
)

type Handler struct {
	mysqlClient *mysql.Client
	orionClient *orion.Client
}

func NewHandler(mysqlClient *mysql.Client, orionClient *orion.Client) *Handler {
	return &Handler{
		mysqlClient: mysqlClient,
		orionClient: orionClient,
	}
}

// SyncAll syncs all reports from MySQL to Orion-LD
func (h *Handler) SyncAll(c *gin.Context) {
	log.Println("Starting full sync...")
	
	// Get all reports from MySQL
	reports, err := h.mysqlClient.GetAllReports()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"success": false,
			"error":   err.Error(),
		})
		return
	}
	
	log.Printf("Found %d reports to sync", len(reports))
	
	// Transform and push to Orion-LD
	successCount := 0
	failCount := 0
	
	for _, report := range reports {
		// Transform to NGSI-LD
		entity := transform.ToNGSILD(report)
		
		// Push to Orion-LD
		if err := h.orionClient.CreateEntity(entity); err != nil {
			log.Printf("Failed to sync report %d: %v", report.ID, err)
			failCount++
		} else {
			successCount++
		}
	}
	
	log.Printf("Sync complete: %d success, %d failed", successCount, failCount)
	
	c.JSON(http.StatusOK, gin.H{
		"success":      true,
		"total":        len(reports),
		"successCount": successCount,
		"failCount":    failCount,
		"message":      "Sync completed",
	})
}

// Health check
func (h *Handler) Health(c *gin.Context) {
	c.JSON(http.StatusOK, gin.H{
		"status": "healthy",
		"service": "context-broker-adapter",
	})
}
