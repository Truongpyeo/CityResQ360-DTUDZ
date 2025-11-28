package main

import (
	"log"
	"os"

	"github.com/cityresq360/wallet-service/internal/config"
	"github.com/cityresq360/wallet-service/internal/models"
	"github.com/cityresq360/wallet-service/internal/routes"
	"github.com/gin-gonic/gin"
	"github.com/joho/godotenv"
)

func main() {
	// Load .env file
	if err := godotenv.Load(); err != nil {
		log.Println("No .env file found")
	}

	// Connect to Database
	config.ConnectDB()

	// Auto Migrate
	config.DB.AutoMigrate(&models.Wallet{}, &models.Transaction{})

	// Initialize Router
	r := gin.Default()

	// Setup Routes
	routes.SetupRoutes(r)

	// Start Server
	port := os.Getenv("PORT")
	if port == "" {
		port = "8005"
	}

	log.Printf("Wallet Service running on port %s", port)
	r.Run(":" + port)
}
