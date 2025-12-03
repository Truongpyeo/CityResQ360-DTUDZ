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
