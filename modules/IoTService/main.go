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
			"service": "IoTService",
			"status":  "running",
			"version": "0.1.0",
			"message": "IoT data collection and monitoring service",
		})
	})

	r.GET("/health", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{
			"status": "healthy",
		})
	})

	// TODO: Implement IoT endpoints
	v1 := r.Group("/api/v1")
	{
		v1.POST("/data", func(c *gin.Context) {
			c.JSON(http.StatusOK, gin.H{
				"success": true,
				"message": "Endpoint not yet implemented",
			})
		})

		v1.GET("/sensors", func(c *gin.Context) {
			c.JSON(http.StatusOK, gin.H{
				"success": true,
				"data":    []string{},
				"message": "No sensors configured yet",
			})
		})
	}

	port := os.Getenv("PORT")
	if port == "" {
		port = "8004"
	}

	log.Printf("IoTService starting on port %s", port)
	r.Run(":" + port)
}
