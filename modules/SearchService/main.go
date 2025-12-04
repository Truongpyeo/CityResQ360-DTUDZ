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
	"context"
	"fmt"
	"log"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/gin-gonic/gin"
	"searchservice/internal/config"
	"searchservice/internal/handlers"
	"searchservice/internal/meilisearch"
	"searchservice/internal/sync"
)

func main() {
	// Load configuration
	cfg := config.Load()

	// Initialize Meilisearch client
	meiliClient, err := meilisearch.NewClient(cfg.MeiliURL, cfg.MeiliKey)
	if err != nil {
		log.Fatalf("Failed to initialize Meilisearch client: %v", err)
	}
	log.Println("✓ Connected to Meilisearch")

	// Initialize syncer
	syncer, err := sync.NewSyncer(cfg, meiliClient)
	if err != nil {
		log.Fatalf("Failed to initialize syncer: %v", err)
	}
	defer syncer.Close()
	log.Println("✓ Database connection established")

	// Setup Gin router
	router := gin.Default()

	// Health check
	router.GET("/", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{
			"service": "SearchService",
			"status":  "running",
			"version": "1.0.0",
			"message": "Advanced search service with Meilisearch",
		})
	})

	router.GET("/health", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{
			"status": "healthy",
			"meilisearch": meiliClient.IsHealthy(),
		})
	})

	// API routes
	api := router.Group("/api/v1")
	{
		handlers.RegisterSearchRoutes(api, meiliClient)
		handlers.RegisterSyncRoutes(api, syncer)
	}

	// HTTP Server
	srv := &http.Server{
		Addr:    fmt.Sprintf(":%s", cfg.Port),
		Handler: router,
	}

	// Graceful shutdown
	go func() {
		log.Printf("SearchService starting on port %s", cfg.Port)
		if err := srv.ListenAndServe(); err != nil && err != http.ErrServerClosed {
			log.Fatalf("Server error: %v", err)
		}
	}()

	// Wait for interrupt signal
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit

	log.Println("Shutting down server...")
	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()

	if err := srv.Shutdown(ctx); err != nil {
		log.Fatal("Server forced to shutdown:", err)
	}

	log.Println("Server exited")
}
