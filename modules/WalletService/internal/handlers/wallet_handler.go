package handlers

import (
	"net/http"
	"strconv"

	"github.com/cityresq360/wallet-service/internal/config"
	"github.com/cityresq360/wallet-service/internal/models"
	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

func GetBalance(c *gin.Context) {
	// In a real app, get UserID from JWT middleware
	userIDStr := c.GetHeader("X-User-ID")
	if userIDStr == "" {
		c.JSON(http.StatusUnauthorized, gin.H{"error": "Unauthorized"})
		return
	}

	userID, _ := strconv.ParseUint(userIDStr, 10, 64)

	var wallet models.Wallet
	result := config.DB.First(&wallet, "user_id = ?", userID)

	if result.Error != nil {
		if result.Error == gorm.ErrRecordNotFound {
			// Create wallet if not exists
			wallet = models.Wallet{UserID: uint(userID), Balance: 0}
			config.DB.Create(&wallet)
		} else {
			c.JSON(http.StatusInternalServerError, gin.H{"error": result.Error.Error()})
			return
		}
	}

	c.JSON(http.StatusOK, gin.H{"balance": wallet.Balance})
}

func GetTransactions(c *gin.Context) {
	userIDStr := c.GetHeader("X-User-ID")
	if userIDStr == "" {
		c.JSON(http.StatusUnauthorized, gin.H{"error": "Unauthorized"})
		return
	}
	userID, _ := strconv.ParseUint(userIDStr, 10, 64)

	var wallet models.Wallet
	if err := config.DB.First(&wallet, "user_id = ?", userID).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Wallet not found"})
		return
	}

	var transactions []models.Transaction
	config.DB.Where("wallet_id = ?", wallet.ID).Order("created_at desc").Limit(20).Find(&transactions)

	c.JSON(http.StatusOK, transactions)
}

type TransactionRequest struct {
	UserID      uint    `json:"user_id"`
	Amount      float64 `json:"amount"`
	Type        string  `json:"type"` // credit, debit
	Description string  `json:"description"`
	ReferenceID string  `json:"reference_id"`
}

// Internal endpoint for other services to adjust balance
func ProcessTransaction(c *gin.Context) {
	var req TransactionRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	tx := config.DB.Begin()

	var wallet models.Wallet
	if err := tx.First(&wallet, "user_id = ?", req.UserID).Error; err != nil {
		if err == gorm.ErrRecordNotFound {
			wallet = models.Wallet{UserID: req.UserID, Balance: 0}
			tx.Create(&wallet)
		} else {
			tx.Rollback()
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
	}

	if req.Type == "debit" {
		if wallet.Balance < req.Amount {
			tx.Rollback()
			c.JSON(http.StatusBadRequest, gin.H{"error": "Insufficient funds"})
			return
		}
		wallet.Balance -= req.Amount
	} else {
		wallet.Balance += req.Amount
	}

	if err := tx.Save(&wallet).Error; err != nil {
		tx.Rollback()
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	transaction := models.Transaction{
		WalletID:    wallet.ID,
		Type:        req.Type,
		Amount:      req.Amount,
		Description: req.Description,
		ReferenceID: req.ReferenceID,
	}

	if err := tx.Create(&transaction).Error; err != nil {
		tx.Rollback()
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	tx.Commit()
	c.JSON(http.StatusOK, gin.H{"message": "Transaction successful", "new_balance": wallet.Balance})
}
