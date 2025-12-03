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
