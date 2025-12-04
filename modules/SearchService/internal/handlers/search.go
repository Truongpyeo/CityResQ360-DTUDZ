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

	"github.com/gin-gonic/gin"
	"searchservice/internal/meilisearch"
	"searchservice/internal/models"
)

// RegisterSearchRoutes registers search-related routes
func RegisterSearchRoutes(router *gin.RouterGroup, meili *meilisearch.Client) {
	router.GET("/search", searchHandler(meili))
	router.GET("/stats", statsHandler(meili))
}

// searchHandler handles search requests
func searchHandler(meili *meilisearch.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		var req models.SearchRequest

		// Parse query parameters
		if err := c.ShouldBindQuery(&req); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"error": "Invalid query parameters",
			})
			return
		}

		// Set defaults
		if req.Limit == 0 {
			req.Limit = 20
		}
		if req.Limit > 100 {
			req.Limit = 100
		}

		// Execute search
		result, err := meili.Search(&req)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{
				"error": "Search failed",
			})
			return
		}

		c.JSON(http.StatusOK, result)
	}
}

// statsHandler returns index statistics
func statsHandler(meili *meilisearch.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		stats, err := meili.GetStats()
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{
				"error": "Failed to get stats",
			})
			return
		}

		c.JSON(http.StatusOK, stats)
	}
}
