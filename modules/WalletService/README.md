# 💰 Wallet Service - CityPoints Reward System

> **Service quản lý điểm thưởng (CityPoints) cho người dùng**

**Port:** 8005  
**Tech Stack:** Go 1.21 + Gin + PostgreSQL + Redis  
**Status:** 📋 Ready to Implement  
**Priority:** 🟡 IMPORTANT - Cần trong tuần 2

---

## 📋 **MỤC LỤC**

1. [Tổng quan](#1-tổng-quan)
2. [Kiến trúc](#2-kiến-trúc)
3. [Database Schema](#3-database-schema)
4. [API Endpoints](#4-api-endpoints)
5. [Event Consumers](#5-event-consumers)
6. [Reward Rules](#6-reward-rules)
7. [Setup Guide](#7-setup-guide)
8. [Implementation](#8-implementation)

---

## **1. TỔNG QUAN**

### 🎯 **Chức năng chính**

- ✅ Quản lý điểm thưởng (CityPoints)
- ✅ Tự động cộng điểm khi có event (report created, resolved, voted)
- ✅ Đổi điểm lấy quà tặng/voucher
- ✅ Lịch sử giao dịch điểm
- ✅ Bảng xếp hạng (Leaderboard)
- ✅ Thành tích (Achievements/Badges)
- ✅ Event-driven architecture

### 🔄 **Luồng hoạt động**

```
User creates report
    ↓
CoreAPI publishes "reports.created"
    ↓
Event Bus (RabbitMQ)
    ↓
WalletService consumes event
    ↓
Add points (+10 points)
    ↓
Save transaction to DB
    ↓
Update user balance
    ↓
Publish "wallet.points_earned"
    ↓
NotificationService sends push notification
```

---

## **2. KIẾN TRÚC**

### 📁 **Project Structure**

```
WalletService/
├── cmd/
│   └── server/
│       └── main.go              # Entry point
├── internal/
│   ├── config/
│   │   └── config.go            # Configuration
│   ├── models/
│   │   ├── wallet.go            # Wallet model
│   │   ├── transaction.go       # Transaction model
│   │   ├── reward.go            # Reward model
│   │   └── achievement.go       # Achievement model
│   ├── handlers/
│   │   ├── wallet_handler.go    # HTTP handlers
│   │   ├── reward_handler.go
│   │   └── leaderboard_handler.go
│   ├── services/
│   │   ├── wallet_service.go    # Business logic
│   │   ├── reward_service.go
│   │   └── achievement_service.go
│   ├── repository/
│   │   ├── wallet_repo.go       # Database operations
│   │   ├── transaction_repo.go
│   │   └── reward_repo.go
│   ├── consumers/
│   │   ├── report_consumer.go   # Listen report events
│   │   └── incident_consumer.go # Listen incident events
│   └── middleware/
│       ├── auth.go              # JWT verification
│       └── logger.go            # Request logging
├── pkg/
│   ├── database/
│   │   └── postgres.go          # PostgreSQL connection
│   ├── redis/
│   │   └── redis.go             # Redis connection
│   ├── rabbitmq/
│   │   └── rabbitmq.go          # RabbitMQ connection
│   └── utils/
│       └── response.go          # Response helpers
├── migrations/
│   ├── 001_create_wallets.sql
│   ├── 002_create_transactions.sql
│   ├── 003_create_rewards.sql
│   └── 004_create_achievements.sql
├── .env.example
├── go.mod
├── go.sum
├── Dockerfile
└── README.md
```

---

## **3. DATABASE SCHEMA**

### 📊 **PostgreSQL Tables**

#### **Table: wallets**

```sql
CREATE TABLE wallets (
    id SERIAL PRIMARY KEY,
    nguoi_dung_id INTEGER NOT NULL UNIQUE,
    so_du_hien_tai INTEGER DEFAULT 0,
    tong_diem_kiem_duoc INTEGER DEFAULT 0,
    tong_diem_da_tieu INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_wallets_nguoi_dung_id ON wallets(nguoi_dung_id);
```

#### **Table: transactions**

```sql
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    wallet_id INTEGER NOT NULL REFERENCES wallets(id),
    nguoi_dung_id INTEGER NOT NULL,
    loai_giao_dich VARCHAR(50) NOT NULL, -- 'earn' | 'redeem'
    so_diem INTEGER NOT NULL,
    ly_do VARCHAR(100) NOT NULL, -- 'create_report', 'report_resolved', 'vote_report', 'redeem_reward'
    mo_ta TEXT,
    lien_ket_den VARCHAR(50), -- 'phan_anh', 'su_co', 'qua_tang'
    id_lien_ket INTEGER,
    so_du_truoc INTEGER NOT NULL,
    so_du_sau INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_transactions_wallet_id ON transactions(wallet_id);
CREATE INDEX idx_transactions_nguoi_dung_id ON transactions(nguoi_dung_id);
CREATE INDEX idx_transactions_created_at ON transactions(created_at DESC);
```

#### **Table: rewards**

```sql
CREATE TABLE rewards (
    id SERIAL PRIMARY KEY,
    ten_qua_tang VARCHAR(255) NOT NULL,
    mo_ta TEXT,
    hinh_anh VARCHAR(500),
    so_diem_can INTEGER NOT NULL,
    so_luong_kho INTEGER DEFAULT 0,
    da_doi INTEGER DEFAULT 0,
    loai VARCHAR(50) NOT NULL, -- 'voucher', 'gift', 'merchandise'
    nha_tai_tro VARCHAR(255),
    ngay_het_han DATE,
    trang_thai SMALLINT DEFAULT 1, -- 1: active, 0: inactive
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_rewards_trang_thai ON rewards(trang_thai);
CREATE INDEX idx_rewards_so_diem_can ON rewards(so_diem_can);
```

#### **Table: reward_redemptions**

```sql
CREATE TABLE reward_redemptions (
    id SERIAL PRIMARY KEY,
    nguoi_dung_id INTEGER NOT NULL,
    reward_id INTEGER NOT NULL REFERENCES rewards(id),
    transaction_id INTEGER NOT NULL REFERENCES transactions(id),
    so_diem_tieu INTEGER NOT NULL,
    ma_voucher VARCHAR(100),
    trang_thai VARCHAR(50) DEFAULT 'pending', -- 'pending', 'approved', 'delivered', 'cancelled'
    ngay_doi TIMESTAMP DEFAULT NOW(),
    ngay_giao DATE,
    ghi_chu TEXT
);

CREATE INDEX idx_redemptions_nguoi_dung_id ON reward_redemptions(nguoi_dung_id);
CREATE INDEX idx_redemptions_trang_thai ON reward_redemptions(trang_thai);
```

#### **Table: achievements**

```sql
CREATE TABLE achievements (
    id SERIAL PRIMARY KEY,
    ten_thanh_tich VARCHAR(255) NOT NULL,
    mo_ta TEXT,
    icon VARCHAR(500),
    dieu_kien JSONB NOT NULL, -- {"type": "reports_count", "threshold": 10}
    diem_thuong INTEGER DEFAULT 0,
    level VARCHAR(50), -- 'bronze', 'silver', 'gold', 'platinum'
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE user_achievements (
    id SERIAL PRIMARY KEY,
    nguoi_dung_id INTEGER NOT NULL,
    achievement_id INTEGER NOT NULL REFERENCES achievements(id),
    ngay_dat_duoc TIMESTAMP DEFAULT NOW(),
    UNIQUE(nguoi_dung_id, achievement_id)
);

CREATE INDEX idx_user_achievements_nguoi_dung_id ON user_achievements(nguoi_dung_id);
```

---

## **4. API ENDPOINTS**

### 📍 **Base URL:** `http://localhost:8005/api/v1`

### **4.1. Get Wallet Balance**

```http
GET /api/v1/wallet
Headers:
  Authorization: Bearer {token}

Response: 200
{
  "success": true,
  "data": {
    "id": 123,
    "nguoi_dung_id": 456,
    "so_du_hien_tai": 250,
    "tong_diem_kiem_duoc": 300,
    "tong_diem_da_tieu": 50,
    "created_at": "2025-11-01T10:00:00Z",
    "updated_at": "2025-11-22T10:30:00Z"
  }
}
```

### **4.2. Get Transaction History**

```http
GET /api/v1/wallet/transactions
Headers:
  Authorization: Bearer {token}
Query:
  ?page=1&limit=20&loai_giao_dich=earn

Response: 200
{
  "success": true,
  "data": [
    {
      "id": 789,
      "loai_giao_dich": "earn",
      "so_diem": 10,
      "ly_do": "create_report",
      "mo_ta": "Tạo phản ánh #12345",
      "lien_ket_den": "phan_anh",
      "id_lien_ket": 12345,
      "so_du_truoc": 240,
      "so_du_sau": 250,
      "created_at": "2025-11-22T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
```

### **4.3. Get Available Rewards**

```http
GET /api/v1/rewards
Query:
  ?page=1&limit=20&loai=voucher

Response: 200
{
  "success": true,
  "data": [
    {
      "id": 5,
      "ten_qua_tang": "Voucher Grab 50K",
      "mo_ta": "Mã giảm giá Grab trị giá 50.000đ",
      "hinh_anh": "https://storage/rewards/grab.png",
      "so_diem_can": 100,
      "so_luong_kho": 50,
      "da_doi": 20,
      "loai": "voucher",
      "nha_tai_tro": "Grab Vietnam",
      "ngay_het_han": "2025-12-31"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 10
  }
}
```

### **4.4. Redeem Reward**

```http
POST /api/v1/rewards/:id/redeem
Headers:
  Authorization: Bearer {token}

Response: 201
{
  "success": true,
  "message": "Đổi quà thành công",
  "data": {
    "redemption_id": 999,
    "reward_id": 5,
    "so_diem_tieu": 100,
    "ma_voucher": "GRAB-ABC123-XYZ",
    "trang_thai": "approved",
    "ngay_doi": "2025-11-22T10:30:00Z",
    "new_balance": 150
  }
}
```

### **4.5. Get Leaderboard**

```http
GET /api/v1/leaderboard
Query:
  ?period=week&limit=100

Response: 200
{
  "success": true,
  "data": [
    {
      "rank": 1,
      "nguoi_dung_id": 123,
      "ho_ten": "Nguyễn Văn A",
      "anh_dai_dien": "https://storage/avatars/123.jpg",
      "tong_diem": 500,
      "so_phan_anh": 25
    },
    {
      "rank": 2,
      "nguoi_dung_id": 456,
      "ho_ten": "Trần Thị B",
      "anh_dai_dien": "https://storage/avatars/456.jpg",
      "tong_diem": 450,
      "so_phan_anh": 22
    }
  ],
  "meta": {
    "period": "week",
    "total_users": 100,
    "my_rank": 15
  }
}
```

### **4.6. Get Achievements**

```http
GET /api/v1/achievements
Headers:
  Authorization: Bearer {token}

Response: 200
{
  "success": true,
  "data": {
    "earned": [
      {
        "id": 1,
        "ten_thanh_tich": "Người khởi đầu",
        "mo_ta": "Tạo phản ánh đầu tiên",
        "icon": "https://storage/badges/starter.png",
        "level": "bronze",
        "diem_thuong": 5,
        "ngay_dat_duoc": "2025-11-01T10:00:00Z"
      }
    ],
    "available": [
      {
        "id": 2,
        "ten_thanh_tich": "Người đóng góp tích cực",
        "mo_ta": "Tạo 10 phản ánh",
        "icon": "https://storage/badges/active.png",
        "level": "silver",
        "diem_thuong": 20,
        "progress": {
          "current": 5,
          "target": 10
        }
      }
    ]
  }
}
```

---

## **5. EVENT CONSUMERS**

### 📡 **Report Events Consumer**

**File: `internal/consumers/report_consumer.go`**

```go
package consumers

import (
    "encoding/json"
    "log"
    "github.com/streadway/amqp"
)

type ReportConsumer struct {
    walletService *services.WalletService
    conn          *amqp.Connection
    channel       *amqp.Channel
}

type ReportEvent struct {
    EventID   string                 `json:"event_id"`
    EventType string                 `json:"event_type"`
    Timestamp string                 `json:"timestamp"`
    Data      map[string]interface{} `json:"data"`
}

func NewReportConsumer(walletService *services.WalletService, rabbitmqURL string) (*ReportConsumer, error) {
    conn, err := amqp.Dial(rabbitmqURL)
    if err != nil {
        return nil, err
    }

    channel, err := conn.Channel()
    if err != nil {
        return nil, err
    }

    return &ReportConsumer{
        walletService: walletService,
        conn:          conn,
        channel:       channel,
    }, nil
}

func (c *ReportConsumer) Start() error {
    exchange := "cityresq.reports"
    queue := "wallet.reports"

    // Declare exchange
    err := c.channel.ExchangeDeclare(
        exchange,
        "topic",
        true,  // durable
        false, // auto-deleted
        false, // internal
        false, // no-wait
        nil,
    )
    if err != nil {
        return err
    }

    // Declare queue
    q, err := c.channel.QueueDeclare(
        queue,
        true,  // durable
        false, // delete when unused
        false, // exclusive
        false, // no-wait
        nil,
    )
    if err != nil {
        return err
    }

    // Bind queue to exchange
    err = c.channel.QueueBind(
        q.Name,
        "reports.created",
        exchange,
        false,
        nil,
    )
    if err != nil {
        return err
    }

    err = c.channel.QueueBind(
        q.Name,
        "reports.status_changed",
        exchange,
        false,
        nil,
    )
    if err != nil {
        return err
    }

    err = c.channel.QueueBind(
        q.Name,
        "reports.voted",
        exchange,
        false,
        nil,
    )
    if err != nil {
        return err
    }

    log.Printf("✅ Listening for events on queue: %s", queue)

    // Consume messages
    msgs, err := c.channel.Consume(
        q.Name,
        "",    // consumer
        false, // auto-ack
        false, // exclusive
        false, // no-local
        false, // no-wait
        nil,
    )
    if err != nil {
        return err
    }

    go func() {
        for msg := range msgs {
            c.handleMessage(msg)
        }
    }()

    return nil
}

func (c *ReportConsumer) handleMessage(msg amqp.Delivery) {
    var event ReportEvent
    err := json.Unmarshal(msg.Body, &event)
    if err != nil {
        log.Printf("❌ Error parsing event: %v", err)
        msg.Nack(false, false)
        return
    }

    log.Printf("📨 Received event: %s [%s]", event.EventType, event.EventID)

    err = c.processEvent(event)
    if err != nil {
        log.Printf("❌ Error processing event: %v", err)
        msg.Nack(false, false)
        return
    }

    msg.Ack(false)
}

func (c *ReportConsumer) processEvent(event ReportEvent) error {
    switch event.EventType {
    case "reports.created":
        return c.handleReportCreated(event.Data)
    case "reports.status_changed":
        return c.handleReportStatusChanged(event.Data)
    case "reports.voted":
        return c.handleReportVoted(event.Data)
    default:
        log.Printf("⚠️ Unknown event type: %s", event.EventType)
        return nil
    }
}

func (c *ReportConsumer) handleReportCreated(data map[string]interface{}) error {
    userID := int(data["nguoi_dung_id"].(float64))
    reportID := int(data["id"].(float64))

    // Add points for creating report
    return c.walletService.AddPoints(userID, 10, "create_report", 
        fmt.Sprintf("Tạo phản ánh #%d", reportID), "phan_anh", reportID)
}

func (c *ReportConsumer) handleReportStatusChanged(data map[string]interface{}) error {
    oldStatus := int(data["trang_thai_cu"].(float64))
    newStatus := int(data["trang_thai_moi"].(float64))

    // If report resolved (status 3)
    if oldStatus != 3 && newStatus == 3 {
        userID := int(data["nguoi_dung_id"].(float64))
        reportID := int(data["id"].(float64))

        // Add bonus points
        return c.walletService.AddPoints(userID, 20, "report_resolved",
            fmt.Sprintf("Phản ánh #%d đã được giải quyết", reportID), "phan_anh", reportID)
    }

    return nil
}

func (c *ReportConsumer) handleReportVoted(data map[string]interface{}) error {
    userID := int(data["nguoi_dung_id"].(float64))
    reportID := int(data["phan_anh_id"].(float64))

    // Add small points for voting
    return c.walletService.AddPoints(userID, 1, "vote_report",
        fmt.Sprintf("Vote phản ánh #%d", reportID), "phan_anh", reportID)
}

func (c *ReportConsumer) Close() {
    if c.channel != nil {
        c.channel.Close()
    }
    if c.conn != nil {
        c.conn.Close()
    }
}
```

---

## **6. REWARD RULES**

### 💎 **Điểm thưởng**

| Hành động | Điểm | Mô tả |
|-----------|------|-------|
| Tạo phản ánh | **+10** | Mỗi phản ánh hợp lệ |
| Phản ánh được giải quyết | **+20** | Bonus khi phản ánh được xử lý |
| Vote phản ánh | **+1** | Mỗi lần vote |
| Bình luận | **+2** | Bình luận có ích |
| Phản ánh được nhiều vote (>50) | **+10** | Phản ánh hot |
| Phản ánh khẩn cấp được giải quyết nhanh | **+30** | Giải quyết < 24h |
| Check-in hàng ngày | **+5** | Đăng nhập mỗi ngày |
| Tuần đầu tiên | **+50** | Thưởng người dùng mới |

### 🎁 **Quà tặng mẫu**

| Quà tặng | Điểm cần | Loại | Nhà tài trợ |
|----------|----------|------|-------------|
| Voucher Grab 50K | **100** | Voucher | Grab Vietnam |
| Voucher Shopee 100K | **200** | Voucher | Shopee |
| Áo thun CityResQ360 | **300** | Merchandise | CityResQ360 |
| Mũ lưỡi trai | **250** | Merchandise | CityResQ360 |
| Voucher The Coffee House 50K | **150** | Voucher | The Coffee House |
| Voucher Circle K 30K | **80** | Voucher | Circle K |
| Túi vải canvas | **200** | Merchandise | CityResQ360 |

### 🏆 **Thành tích (Achievements)**

| Tên | Điều kiện | Điểm thưởng | Level |
|-----|-----------|-------------|-------|
| Người khởi đầu | Tạo phản ánh đầu tiên | +5 | Bronze |
| Người đóng góp tích cực | Tạo 10 phản ánh | +20 | Silver |
| Chiến binh thành phố | Tạo 50 phản ánh | +100 | Gold |
| Huyền thoại | Tạo 100 phản ánh | +500 | Platinum |
| Người có tầm nhìn | Phản ánh được 100+ votes | +50 | Gold |
| Người truyền cảm hứng | 10 phản ánh được giải quyết | +100 | Gold |
| Người bình luận tích cực | 50 bình luận | +30 | Silver |

---

## **7. SETUP GUIDE**

### 📦 **Installation**

```bash
mkdir WalletService
cd WalletService

# Initialize Go module
go mod init github.com/cityresq360/wallet-service

# Install dependencies
go get -u github.com/gin-gonic/gin
go get -u github.com/lib/pq
go get -u github.com/go-redis/redis/v8
go get -u github.com/streadway/amqp
go get -u github.com/golang-jwt/jwt/v4
go get -u github.com/joho/godotenv
```

### 🔧 **Environment Variables**

**File: `.env.example`**

```env
# Server
PORT=8005
ENV=development

# PostgreSQL
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=walletservice
POSTGRES_PASSWORD=walletservice_password
POSTGRES_DB=wallet_db

# Redis
REDIS_URL=redis://localhost:6379

# RabbitMQ
RABBITMQ_URL=amqp://cityresq:cityresq_password@localhost:5672

# JWT
JWT_SECRET=your-secret-key

# Event Publishing
EVENT_EXCHANGE=cityresq.wallet
```

---

## **8. IMPLEMENTATION**

### 📝 **Main Server**

**File: `cmd/server/main.go`**

```go
package main

import (
    "log"
    "os"

    "github.com/gin-gonic/gin"
    "github.com/joho/godotenv"
    
    "github.com/cityresq360/wallet-service/internal/config"
    "github.com/cityresq360/wallet-service/internal/handlers"
    "github.com/cityresq360/wallet-service/internal/repository"
    "github.com/cityresq360/wallet-service/internal/services"
    "github.com/cityresq360/wallet-service/internal/consumers"
    "github.com/cityresq360/wallet-service/pkg/database"
    "github.com/cityresq360/wallet-service/pkg/redis"
)

func main() {
    // Load environment variables
    if err := godotenv.Load(); err != nil {
        log.Println("No .env file found")
    }

    // Load config
    cfg := config.LoadConfig()

    // Connect to PostgreSQL
    db, err := database.NewPostgresDB(cfg)
    if err != nil {
        log.Fatalf("Failed to connect to database: %v", err)
    }
    defer db.Close()

    // Connect to Redis
    rdb := redis.NewRedisClient(cfg)
    defer rdb.Close()

    // Initialize repositories
    walletRepo := repository.NewWalletRepository(db)
    transactionRepo := repository.NewTransactionRepository(db)
    rewardRepo := repository.NewRewardRepository(db)

    // Initialize services
    walletService := services.NewWalletService(walletRepo, transactionRepo, rdb)
    rewardService := services.NewRewardService(rewardRepo, walletService)

    // Initialize handlers
    walletHandler := handlers.NewWalletHandler(walletService)
    rewardHandler := handlers.NewRewardHandler(rewardService)

    // Start event consumers
    reportConsumer, err := consumers.NewReportConsumer(walletService, cfg.RabbitMQURL)
    if err != nil {
        log.Fatalf("Failed to create report consumer: %v", err)
    }
    defer reportConsumer.Close()

    if err := reportConsumer.Start(); err != nil {
        log.Fatalf("Failed to start report consumer: %v", err)
    }

    // Setup Gin router
    r := gin.Default()

    // Health check
    r.GET("/health", func(c *gin.Context) {
        c.JSON(200, gin.H{"status": "ok", "service": "WalletService"})
    })

    // API routes
    api := r.Group("/api/v1")
    {
        // Wallet endpoints
        api.GET("/wallet", walletHandler.GetWallet)
        api.GET("/wallet/transactions", walletHandler.GetTransactions)

        // Reward endpoints
        api.GET("/rewards", rewardHandler.GetRewards)
        api.POST("/rewards/:id/redeem", rewardHandler.RedeemReward)
        api.GET("/rewards/my-redemptions", rewardHandler.GetMyRedemptions)

        // Leaderboard
        api.GET("/leaderboard", walletHandler.GetLeaderboard)

        // Achievements
        api.GET("/achievements", walletHandler.GetAchievements)
    }

    // Start server
    port := os.Getenv("PORT")
    if port == "" {
        port = "8005"
    }

    log.Printf("🚀 Wallet Service running on port %s", port)
    if err := r.Run(":" + port); err != nil {
        log.Fatalf("Failed to start server: %v", err)
    }
}
```

---

### 📝 **Wallet Service**

**File: `internal/services/wallet_service.go`**

```go
package services

import (
    "context"
    "fmt"
    "time"

    "github.com/cityresq360/wallet-service/internal/models"
    "github.com/cityresq360/wallet-service/internal/repository"
    "github.com/go-redis/redis/v8"
)

type WalletService struct {
    walletRepo      *repository.WalletRepository
    transactionRepo *repository.TransactionRepository
    redis           *redis.Client
}

func NewWalletService(
    walletRepo *repository.WalletRepository,
    transactionRepo *repository.TransactionRepository,
    rdb *redis.Client,
) *WalletService {
    return &WalletService{
        walletRepo:      walletRepo,
        transactionRepo: transactionRepo,
        redis:           rdb,
    }
}

// GetOrCreateWallet gets or creates a wallet for a user
func (s *WalletService) GetOrCreateWallet(userID int) (*models.Wallet, error) {
    wallet, err := s.walletRepo.GetByUserID(userID)
    if err == nil {
        return wallet, nil
    }

    // Create new wallet if not exists
    wallet = &models.Wallet{
        NguoiDungID:       userID,
        SoDuHienTai:       0,
        TongDiemKiemDuoc:  0,
        TongDiemDaTieu:    0,
    }

    err = s.walletRepo.Create(wallet)
    return wallet, err
}

// AddPoints adds points to a user's wallet
func (s *WalletService) AddPoints(
    userID int,
    points int,
    reason string,
    description string,
    linkedTo string,
    linkedID int,
) error {
    ctx := context.Background()

    // Get or create wallet
    wallet, err := s.GetOrCreateWallet(userID)
    if err != nil {
        return err
    }

    // Create transaction
    transaction := &models.Transaction{
        WalletID:      wallet.ID,
        NguoiDungID:   userID,
        LoaiGiaoDich:  "earn",
        SoDiem:        points,
        LyDo:          reason,
        MoTa:          description,
        LienKetDen:    linkedTo,
        IDLienKet:     linkedID,
        SoDuTruoc:     wallet.SoDuHienTai,
        SoDuSau:       wallet.SoDuHienTai + points,
    }

    err = s.transactionRepo.Create(transaction)
    if err != nil {
        return err
    }

    // Update wallet balance
    wallet.SoDuHienTai += points
    wallet.TongDiemKiemDuoc += points
    err = s.walletRepo.Update(wallet)
    if err != nil {
        return err
    }

    // Clear cache
    s.redis.Del(ctx, fmt.Sprintf("wallet:%d", userID))

    // TODO: Publish wallet.points_earned event

    return nil
}

// DeductPoints deducts points from a user's wallet
func (s *WalletService) DeductPoints(
    userID int,
    points int,
    reason string,
    description string,
    linkedTo string,
    linkedID int,
) error {
    ctx := context.Background()

    wallet, err := s.GetOrCreateWallet(userID)
    if err != nil {
        return err
    }

    // Check sufficient balance
    if wallet.SoDuHienTai < points {
        return fmt.Errorf("insufficient balance")
    }

    // Create transaction
    transaction := &models.Transaction{
        WalletID:      wallet.ID,
        NguoiDungID:   userID,
        LoaiGiaoDich:  "redeem",
        SoDiem:        points,
        LyDo:          reason,
        MoTa:          description,
        LienKetDen:    linkedTo,
        IDLienKet:     linkedID,
        SoDuTruoc:     wallet.SoDuHienTai,
        SoDuSau:       wallet.SoDuHienTai - points,
    }

    err = s.transactionRepo.Create(transaction)
    if err != nil {
        return err
    }

    // Update wallet balance
    wallet.SoDuHienTai -= points
    wallet.TongDiemDaTieu += points
    err = s.walletRepo.Update(wallet)
    if err != nil {
        return err
    }

    // Clear cache
    s.redis.Del(ctx, fmt.Sprintf("wallet:%d", userID))

    return nil
}

// GetTransactions gets transaction history for a user
func (s *WalletService) GetTransactions(userID int, page, limit int, transactionType string) ([]*models.Transaction, int, error) {
    return s.transactionRepo.GetByUserID(userID, page, limit, transactionType)
}
```

---

## **9. DOCKER COMPOSE**

**File: `docker-compose.yml`**

```yaml
version: '3.8'

services:
  postgres:
    image: postgres:16-alpine
    container_name: wallet-postgres
    ports:
      - "5433:5432"
    environment:
      POSTGRES_USER: walletservice
      POSTGRES_PASSWORD: walletservice_password
      POSTGRES_DB: wallet_db
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./migrations:/docker-entrypoint-initdb.d
    networks:
      - cityresq-network

  redis:
    image: redis:7-alpine
    container_name: wallet-redis
    ports:
      - "6381:6379"
    networks:
      - cityresq-network

  wallet-service:
    build: .
    container_name: wallet-service
    ports:
      - "8005:8005"
    depends_on:
      - postgres
      - redis
    environment:
      PORT: 8005
      POSTGRES_HOST: postgres
      POSTGRES_PORT: 5432
      POSTGRES_USER: walletservice
      POSTGRES_PASSWORD: walletservice_password
      POSTGRES_DB: wallet_db
      REDIS_URL: redis://redis:6379
      RABBITMQ_URL: amqp://cityresq:cityresq_password@rabbitmq:5672
    networks:
      - cityresq-network

volumes:
  postgres_data:

networks:
  cityresq-network:
    external: true
```

---

## **10. TESTING**

### 🧪 **Manual Test**

```bash
# Get wallet balance
curl http://localhost:8005/api/v1/wallet \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get transactions
curl http://localhost:8005/api/v1/wallet/transactions?page=1&limit=20 \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get rewards
curl http://localhost:8005/api/v1/rewards

# Redeem reward
curl -X POST http://localhost:8005/api/v1/rewards/5/redeem \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get leaderboard
curl http://localhost:8005/api/v1/leaderboard?period=week
```

---

## **11. NEXT STEPS**

### ✅ **Phase 1: Core Features** (Week 2)
- [ ] Setup PostgreSQL & Redis
- [ ] Implement Wallet CRUD
- [ ] Implement Transaction history
- [ ] Implement Event consumers
- [ ] Test point earning flow

### 🎯 **Phase 2: Rewards** (Week 3)
- [ ] Implement Reward catalog
- [ ] Implement Redemption flow
- [ ] Generate voucher codes
- [ ] Admin approval flow

### 🏆 **Phase 3: Gamification** (Week 4)
- [ ] Implement Leaderboard
- [ ] Implement Achievements
- [ ] Implement Daily check-in
- [ ] Implement Badge system

---

**Last Updated:** November 22, 2025  
**Status:** 📋 Ready to implement  
**Priority:** 🟡 IMPORTANT
