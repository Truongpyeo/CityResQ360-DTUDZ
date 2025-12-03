package handlers

import (
	"net/http"
	"sync"
	
	"github.com/gin-gonic/gin"
	"iotadapter/internal/models"
)

type Handler struct {
	stats *models.SensorStats
	mu    sync.RWMutex
}

func NewHandler() *Handler {
	return &Handler{
		stats: &models.SensorStats{},
	}
}

// Health check
func (h *Handler) Health(c *gin.Context) {
	c.JSON(http.StatusOK, gin.H{
		"status":  "healthy",
		"service": "iot-adapter",
	})
}

// GetStats returns sensor statistics
func (h *Handler) GetStats(c *gin.Context) {
	h.mu.RLock()
	defer h.mu.RUnlock()
	
	c.JSON(http.StatusOK, h.stats)
}

// Update statistics
func (h *Handler) IncrementRainfall() {
	h.mu.Lock()
	defer h.mu.Unlock()
	h.stats.RainfallMessages++
	h.stats.TotalMessages++
}

func (h *Handler) IncrementAirQuality() {
	h.mu.Lock()
	defer h.mu.Unlock()
	h.stats.AirQualityMessages++
	h.stats.TotalMessages++
}

func (h *Handler) IncrementWindSpeed() {
	h.mu.Lock()
	defer h.mu.Unlock()
	h.stats.WindSpeedMessages++
	h.stats.TotalMessages++
}

func (h *Handler) IncrementEntitiesCreated() {
	h.mu.Lock()
	defer h.mu.Unlock()
	h.stats.EntitiesCreated++
}

func (h *Handler) IncrementAlertsCreated() {
	h.mu.Lock()
	defer h.mu.Unlock()
	h.stats.AlertsCreated++
}
