package main

import (
	"log"
	"os"
	"os/signal"
	"syscall"
	
	"github.com/gin-gonic/gin"
	"contextbrokeradapter/internal/config"
	"contextbrokeradapter/internal/handlers"
	"contextbrokeradapter/internal/mysql"
	"contextbrokeradapter/internal/orion"
)

func main() {
	log.Println("Starting ContextBrokerAdapter...")
	
	// Load config
	cfg := config.Load()
	log.Printf("Config loaded - MySQL: %s:%s, Orion: %s", 
		cfg.MySQLHost, cfg.MySQLPort, cfg.OrionURL)
	
	// Connect to MySQL
	mysqlClient, err := mysql.NewClient(cfg)
	if err != nil {
		log.Fatalf("Failed to connect to MySQL: %v", err)
	}
	defer mysqlClient.Close()
	log.Println("✓ Connected to MySQL")
	
	// Create Orion-LD client
	orionClient := orion.NewClient(cfg)
	log.Println("✓ Orion-LD client initialized")
	
	// Create handler
	h := handlers.NewHandler(mysqlClient, orionClient)
	
	// Setup Gin router
	router := gin.Default()
	
	// Routes
	router.GET("/", func(c *gin.Context) {
		c.JSON(200, gin.H{
			"service": "ContextBrokerAdapter",
			"version": "1.0.0",
			"description": "Sync MySQL Reports to Orion-LD Context Broker",
		})
	})
	
	router.GET("/health", h.Health)
	router.StaticFile("/context.jsonld", "./context.jsonld")
	
	api := router.Group("/api/v1")
	{
		api.POST("/sync", h.SyncAll)
	}
	
	// Start server in goroutine
	go func() {
		addr := ":" + cfg.Port
		log.Printf("Server starting on %s...", addr)
		if err := router.Run(addr); err != nil {
			log.Fatalf("Server failed: %v", err)
		}
	}()
	
	// Graceful shutdown
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit
	
	log.Println("Shutting down...")
}
