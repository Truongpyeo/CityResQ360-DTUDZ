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
