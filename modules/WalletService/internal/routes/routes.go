package routes

import (
	"github.com/cityresq360/wallet-service/internal/handlers"
	"github.com/gin-gonic/gin"
)

func SetupRoutes(r *gin.Engine) {
	v1 := r.Group("/api/v1")
	{
		v1.GET("/wallet", handlers.GetBalance)
		v1.GET("/wallet/transactions", handlers.GetTransactions)
		
		// Internal routes (should be protected by API key in production)
		v1.POST("/wallet/transaction", handlers.ProcessTransaction)
	}
}
