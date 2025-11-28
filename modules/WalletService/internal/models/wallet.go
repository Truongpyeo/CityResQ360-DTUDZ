package models

import (
	"time"

	"gorm.io/gorm"
)

type Wallet struct {
	ID        uint           `gorm:"primaryKey" json:"id"`
	UserID    uint           `gorm:"uniqueIndex;not null" json:"user_id"`
	Balance   float64        `gorm:"default:0" json:"balance"`
	CreatedAt time.Time      `json:"created_at"`
	UpdatedAt time.Time      `json:"updated_at"`
	DeletedAt gorm.DeletedAt `gorm:"index" json:"-"`
}

type Transaction struct {
	ID          uint      `gorm:"primaryKey" json:"id"`
	WalletID    uint      `gorm:"index;not null" json:"wallet_id"`
	Type        string    `gorm:"type:varchar(20);not null" json:"type"` // credit, debit
	Amount      float64   `gorm:"not null" json:"amount"`
	Description string    `json:"description"`
	ReferenceID string    `json:"reference_id"` // e.g., report_id
	CreatedAt   time.Time `json:"created_at"`
}
