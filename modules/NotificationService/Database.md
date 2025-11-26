# Notification Service - Database Schema

## 📋 Thông tin chung

- **Service**: Notification Service
- **Port**: 8006
- **Database Type**: PostgreSQL 15 + Redis 7.2
- **Database Name**: `notification_service_db`
- **Purpose**: Quản lý push notifications, email, SMS, WebSocket

---

## 📊 Danh sách bảng PostgreSQL (1 bảng)

### 1. `thong_baos` - Notification History

**Mục đích**: Lưu trữ lịch sử thông báo đã gửi

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `nguoi_dung_id` | BIGINT | Reference to core_api.nguoi_dungs.id |
| `canh_bao_id` | BIGINT | Reference to incident_service.canh_baos.id |
| `tieu_de` | VARCHAR(150) | Tiêu đề thông báo |
| `noi_dung` | TEXT | Nội dung |
| `kenh_gui` | SMALLINT | 0:app, 1:email, 2:sms, 3:websocket |
| `thoi_gian_gui` | TIMESTAMPTZ | Thời gian gửi |
| `thoi_gian_doc` | TIMESTAMPTZ | Thời gian đọc |
| `la_da_doc` | BOOLEAN | Đã đọc (default: false) |
| `delivery_status` | SMALLINT | 0:pending, 1:sent, 2:failed |
| `retry_count` | INTEGER | Số lần retry (default: 0) |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |

**Indexes**:
- `idx_thong_bao_nguoi_dung` on `nguoi_dung_id, created_at DESC`
- `idx_thong_bao_da_doc` on `la_da_doc`
- `idx_thong_bao_status` on `delivery_status`
- `idx_thong_bao_kenh` on `kenh_gui`

---

## 🔴 Redis Data Structures

### 1. Notification Queue
```redis
# Queue for pending notifications
LPUSH notification:queue:app '{"user_id": 123, "title": "...", "body": "..."}'
LPUSH notification:queue:email '{"to": "user@example.com", ...}'
LPUSH notification:queue:sms '{"phone": "+84901234567", ...}'

# Process queue
BRPOP notification:queue:app 5
```

### 2. Push Tokens (FCM)
```redis
# Store FCM tokens per user
SADD user:123:fcm_tokens "fcm_token_abc123"
SADD user:123:fcm_tokens "fcm_token_def456"

# Get all tokens for a user
SMEMBERS user:123:fcm_tokens

# Remove invalid token
SREM user:123:fcm_tokens "fcm_token_abc123"
```

### 3. Notification Preferences
```redis
# Store user notification preferences
HSET user:123:notify_prefs app true
HSET user:123:notify_prefs email true
HSET user:123:notify_prefs sms false

# Get preferences
HGETALL user:123:notify_prefs
```

### 4. Rate Limiting
```redis
# Rate limit: max 10 notifications per user per hour
INCR user:123:notify_count
EXPIRE user:123:notify_count 3600

# Check limit
GET user:123:notify_count
```

### 5. Unread Count Cache
```redis
# Cache unread notification count
SET user:123:unread_count 5 EX 300

# Increment/decrement
INCR user:123:unread_count
DECR user:123:unread_count
```

### 6. WebSocket Connections
```redis
# Store active WebSocket connections
SADD websocket:active user:123:conn_abc
SADD websocket:active user:456:conn_def

# Publish to WebSocket channel
PUBLISH websocket:user:123 '{"type": "notification", "data": {...}}'
```

---

## 🔗 Quan hệ với các service khác

### Cross-service References (Application Level)
- `thong_baos.nguoi_dung_id` → Core API: `nguoi_dungs.id`
- `thong_baos.canh_bao_id` → Incident Service: `canh_baos.id`

---

## 📨 Event Integration

### Consumed Events
- `reports.created` - Gửi thông báo cho cơ quan liên quan
- `reports.updated` - Thông báo cho người tạo report
- `reports.resolved` - Thông báo giải quyết thành công
- `incident.updated` - Thông báo thay đổi sự cố
- `alert.triggered` - Thông báo cảnh báo khẩn cấp
- `wallet.credited` - Thông báo nhận CityPoint
- `comments.replied` - Thông báo có người comment
- `sensor.threshold_exceeded` - Cảnh báo ngưỡng sensor

### Published Events
- `notification.sent` - Thông báo đã gửi thành công
- `notification.failed` - Thông báo gửi thất bại
- `notification.read` - Người dùng đã đọc thông báo

---

## 🔧 Cấu hình

### PostgreSQL
```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=notification_service_db
DB_USERNAME=notification_user
DB_PASSWORD=notification_password
```

### Redis
```env
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0
```

### Push Notification (FCM)
```env
FCM_SERVER_KEY=your_fcm_server_key
FCM_PROJECT_ID=cityresq360
FCM_BATCH_SIZE=500
```

### Email (SMTP/SendGrid)
```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@cityresq360.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@cityresq360.com
MAIL_FROM_NAME=CityResQ360
```

### SMS (Twilio)
```env
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_PHONE_NUMBER=+1234567890
```

### WebSocket (Socket.io)
```env
WEBSOCKET_PORT=8006
WEBSOCKET_CORS_ORIGIN=*
```

---

## 📝 Notes

### Architecture
- **PostgreSQL**: Lưu lịch sử thông báo lâu dài
- **Redis**: Queue, cache, real-time data
- **FCM** (Firebase Cloud Messaging): Push notification cho mobile
- **Socket.io**: WebSocket cho real-time notifications
- **Bull Queue** (Node.js): Job queue processing

### Notification Channels
1. **App Push** (FCM)
   - Mobile app notifications
   - High priority for alerts
   - Support rich notifications (images, actions)

2. **Email**
   - Digest emails (daily/weekly summary)
   - Important updates
   - HTML templates

3. **SMS**
   - Emergency alerts only
   - Cost-effective (limited usage)
   - High open rate

4. **WebSocket**
   - Real-time updates
   - Dashboard notifications
   - Live feed

### Notification Types
- `report_status_changed` - Trạng thái phản ánh thay đổi
- `report_commented` - Có người bình luận
- `report_resolved` - Phản ánh được giải quyết
- `alert_triggered` - Cảnh báo khẩn cấp
- `wallet_credited` - Nhận CityPoint
- `system_announcement` - Thông báo hệ thống

### Delivery Strategy
1. Check user preferences (Redis)
2. Add to appropriate queue (Redis)
3. Worker processes queue (Bull)
4. Retry on failure (max 3 times)
5. Log to PostgreSQL
6. Update delivery status

---

## 🔍 Example Queries

### Get unread notifications
```sql
SELECT 
  id,
  tieu_de,
  noi_dung,
  kenh_gui,
  created_at
FROM thong_baos
WHERE nguoi_dung_id = $1
  AND la_da_doc = false
ORDER BY created_at DESC
LIMIT 20;
```

### Mark as read
```sql
UPDATE thong_baos
SET 
  la_da_doc = true,
  thoi_gian_doc = NOW(),
  updated_at = NOW()
WHERE id = $1
  AND nguoi_dung_id = $2;
```

### Get notification stats
```sql
SELECT 
  kenh_gui,
  delivery_status,
  COUNT(*) as total
FROM thong_baos
WHERE created_at >= NOW() - INTERVAL '7 days'
GROUP BY kenh_gui, delivery_status;
```

---

## 🛡️ Security & Best Practices

- **Rate limiting**: Max 10 notifications/user/hour
- **User preferences**: Respect opt-out settings
- **Retry logic**: Max 3 retries with exponential backoff
- **Token management**: Remove invalid FCM tokens
- **Data retention**: Auto-delete old notifications (>90 days)
- **GDPR compliance**: Allow users to export/delete their data
- **Encryption**: Encrypt sensitive content in transit
