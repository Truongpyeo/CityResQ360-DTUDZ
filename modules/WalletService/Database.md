# Wallet Service - Database Schema

## 📋 Thông tin chung

- **Service**: Wallet Service
- **Port**: 8005
- **Database Type**: PostgreSQL 15
- **Database Name**: `wallet_service_db`
- **Purpose**: Quản lý ví điện tử CityPoint, giao dịch, rewards

---

## 📊 Danh sách bảng (3 bảng)

### 1. `vi_dien_tus` - Ví điện tử

**Mục đích**: Lưu trữ thông tin ví CityPoint của người dùng

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `nguoi_dung_id` | BIGINT | Reference to core_api.nguoi_dungs.id (UNIQUE) |
| `so_du_hien_tai` | DECIMAL(15,2) | Số dư hiện tại (default: 0) |
| `so_du_tam_giu` | DECIMAL(15,2) | Số dư tạm giữ (default: 0) |
| `tong_nhan` | DECIMAL(15,2) | Tổng CityPoint đã nhận (default: 0) |
| `tong_chi` | DECIMAL(15,2) | Tổng CityPoint đã chi (default: 0) |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |

**Indexes**:
- `idx_vi_nguoi_dung` on `nguoi_dung_id` (UNIQUE)
- `idx_vi_so_du` on `so_du_hien_tai`

**Constraints**:
- `CHECK (so_du_hien_tai >= 0)` - Số dư không âm
- `CHECK (so_du_tam_giu >= 0)` - Số dư tạm giữ không âm

---

### 2. `giao_dich_vi_dien_tus` - Giao dịch CityPoint

**Mục đích**: Lưu trữ lịch sử giao dịch ví điện tử

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `vi_id` | BIGINT | Foreign key to vi_dien_tus.id |
| `nguoi_dung_id` | BIGINT | Reference to core_api.nguoi_dungs.id |
| `so_tien` | DECIMAL(10,2) | Số tiền giao dịch |
| `loai_giao_dich` | SMALLINT | 0:reward, 1:spend, 2:admin_adjust |
| `mo_ta` | VARCHAR(255) | Mô tả giao dịch |
| `ma_giao_dich_hash` | VARCHAR(100) | Mã hash giao dịch (for audit) |
| `phan_anh_lien_quan_id` | BIGINT | Reference to core_api.phan_anhs.id |
| `trang_thai` | SMALLINT | 0:pending, 1:completed, 2:failed (default: 1) |
| `so_du_truoc` | DECIMAL(15,2) | Số dư trước giao dịch |
| `so_du_sau` | DECIMAL(15,2) | Số dư sau giao dịch |
| `metadata` | JSONB | Metadata bổ sung |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |

**Indexes**:
- `idx_giao_dich_vi` on `vi_id, created_at DESC`
- `idx_giao_dich_nguoi_dung` on `nguoi_dung_id, created_at DESC`
- `idx_giao_dich_loai` on `loai_giao_dich`
- `idx_giao_dich_trang_thai` on `trang_thai`
- `idx_giao_dich_hash` on `ma_giao_dich_hash` (UNIQUE)

**Foreign Keys**:
- `vi_id` → `vi_dien_tus(id)` ON DELETE RESTRICT

---

### 3. `lich_su_city_points` - Lịch sử CityPoint

**Mục đích**: Theo dõi chi tiết các thay đổi điểm theo hành động

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `nguoi_dung_id` | BIGINT | Reference to core_api.nguoi_dungs.id |
| `hanh_dong` | VARCHAR(100) | Hành động (report_verified, helpful_comment, etc.) |
| `diem_thay_doi` | INTEGER | Điểm thay đổi (+/-) |
| `ly_do` | TEXT | Lý do chi tiết |
| `created_at` | TIMESTAMPTZ | Thời gian |

**Indexes**:
- `idx_lich_su_nguoi_dung` on `nguoi_dung_id, created_at DESC`
- `idx_lich_su_hanh_dong` on `hanh_dong`

---

## 🎁 Reward Rules (Application Logic)

### Earning Points
| Hành động | Điểm | Điều kiện |
|-----------|------|-----------|
| Tạo phản ánh | +10 | Phản ánh được tạo |
| Phản ánh được xác minh | +50 | Phản ánh hợp lệ |
| Phản ánh được giải quyết | +100 | Sự cố được giải quyết thành công |
| Bình luận hữu ích | +5 | Được upvote > 10 lần |
| Ảnh chất lượng cao | +20 | AI phát hiện object với confidence > 0.9 |
| Phản ánh nhanh | +30 | Là người đầu tiên báo cáo sự cố |

### Spending Points (Optional features)
| Hành động | Điểm | Mô tả |
|-----------|------|-------|
| Ưu tiên phản ánh | -50 | Đẩy phản ánh lên đầu danh sách |
| Tặng badge | -100 | Tặng badge cho người dùng khác |
| Premium features | -500 | Unlock premium features |

### Penalty
| Hành động | Điểm | Điều kiện |
|-----------|------|-----------|
| Phản ánh spam | -20 | Bị admin đánh dấu spam |
| Phản ánh sai | -10 | Bị từ chối vì sai thông tin |

---

## 🔗 Quan hệ với các service khác

### Cross-service References (Application Level)
- `vi_dien_tus.nguoi_dung_id` → Core API: `nguoi_dungs.id`
- `giao_dich_vi_dien_tus.nguoi_dung_id` → Core API: `nguoi_dungs.id`
- `giao_dich_vi_dien_tus.phan_anh_lien_quan_id` → Core API: `phan_anhs.id`
- `lich_su_city_points.nguoi_dung_id` → Core API: `nguoi_dungs.id`

---

## 📨 Event Integration

### Published Events
- `wallet.credited` - Khi ví được cộng điểm
- `wallet.debited` - Khi ví bị trừ điểm
- `wallet.balance_low` - Khi số dư thấp

### Consumed Events
- `reports.verified` - Cộng điểm cho report được xác minh
- `reports.resolved` - Cộng điểm cho report được giải quyết
- `comments.upvoted` - Cộng điểm cho comment hữu ích
- `ai.high_confidence` - Cộng điểm cho ảnh chất lượng cao
- `reports.rejected` - Trừ điểm cho report spam/sai

---

## 🔧 Cấu hình Database

```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=wallet_service_db
DB_USERNAME=wallet_user
DB_PASSWORD=wallet_password
DB_DRIVER=postgresql

# Reward Configuration
REWARD_REPORT_CREATED=10
REWARD_REPORT_VERIFIED=50
REWARD_REPORT_RESOLVED=100
REWARD_HELPFUL_COMMENT=5
REWARD_HIGH_QUALITY_IMAGE=20
REWARD_FIRST_REPORTER=30

PENALTY_SPAM=-20
PENALTY_REJECTED=-10
```

---

## 📝 Notes

- **Transaction hash**: SHA256 hash của (user_id + amount + timestamp) để audit trail
- **Double-entry bookkeeping**: Mỗi giao dịch ghi rõ số dư trước và sau
- **Optimistic locking**: Sử dụng `updated_at` để tránh race condition
- **Idempotency**: Sử dụng `ma_giao_dich_hash` để tránh duplicate transaction
- **Số dư tạm giữ**: Dùng cho giao dịch pending (ví dụ: mua premium features)
- **PostgreSQL được chọn** vì ACID compliance cho financial transactions
- **Decimal(15,2)**: Đủ để lưu 999,999,999,999.99 points

---

## 🔍 Example Queries

### Get wallet balance
```sql
SELECT 
  nguoi_dung_id,
  so_du_hien_tai,
  so_du_tam_giu,
  tong_nhan,
  tong_chi
FROM vi_dien_tus
WHERE nguoi_dung_id = $1;
```

### Transaction history
```sql
SELECT 
  id,
  so_tien,
  loai_giao_dich,
  mo_ta,
  so_du_sau,
  created_at
FROM giao_dich_vi_dien_tus
WHERE nguoi_dung_id = $1
ORDER BY created_at DESC
LIMIT 20;
```

### Top earners leaderboard
```sql
SELECT 
  nguoi_dung_id,
  so_du_hien_tai
FROM vi_dien_tus
ORDER BY so_du_hien_tai DESC
LIMIT 10;
```

### Credit points (with transaction)
```sql
BEGIN;

-- Update wallet
UPDATE vi_dien_tus
SET 
  so_du_hien_tai = so_du_hien_tai + $2,
  tong_nhan = tong_nhan + $2,
  updated_at = NOW()
WHERE nguoi_dung_id = $1
RETURNING id, so_du_hien_tai - $2 AS so_du_truoc, so_du_hien_tai AS so_du_sau;

-- Insert transaction
INSERT INTO giao_dich_vi_dien_tus (
  vi_id, nguoi_dung_id, so_tien, loai_giao_dich, 
  mo_ta, ma_giao_dich_hash, so_du_truoc, so_du_sau
) VALUES (
  $vi_id, $1, $2, 0, 
  'Report verified reward', $hash, $so_du_truoc, $so_du_sau
);

COMMIT;
```

---

## 🛡️ Security

- Row-level security (RLS) - User chỉ xem được ví của mình
- Transaction atomicity - Sử dụng database transaction
- Audit trail - Mọi thay đổi đều được log
- Hash verification - Validate transaction hash
- Rate limiting - Giới hạn số giao dịch/ngày
