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
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"searchservice/internal/sync"
)

// RegisterSyncRoutes registers sync-related routes
func RegisterSyncRoutes(router *gin.RouterGroup, syncer *sync.Syncer) {
	router.POST("/sync", syncAllHandler(syncer))
	router.POST("/sync/recent", syncRecentHandler(syncer))
}

// syncAllHandler triggers a full sync from MySQL to Meilisearch
func syncAllHandler(syncer *sync.Syncer) gin.HandlerFunc {
	return func(c *gin.Context) {
		count, err := syncer.SyncAll()
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{
				"success": false,
				"error":   err.Error(),
			})
			return
		}

		c.JSON(http.StatusOK, gin.H{
			"success":       true,
			"syncedCount":   count,
			"message":       "Full sync completed",
			"completedAt":   time.Now().Format(time.RFC3339),
		})
	}
}

// syncRecentHandler syncs recent updates
func syncRecentHandler(syncer *sync.Syncer) gin.HandlerFunc {
	return func(c *gin.Context) {
		// Default: sync last 24 hours
		since := time.Now().Add(-24 * time.Hour)

		// Optional: parse 'since' parameter
		if sinceParam := c.Query("since"); sinceParam != "" {
			if t, err := time.Parse(time.RFC3339, sinceParam); err == nil {
				since = t
			}
		}

		count, err := syncer.SyncRecent(since)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{
				"success": false,
				"error":   err.Error(),
			})
			return
		}

		c.JSON(http.StatusOK, gin.H{
			"success":     true,
			"syncedCount": count,
			"since":       since.Format(time.RFC3339),
			"message":     "Recent sync completed",
		})
	}
}
