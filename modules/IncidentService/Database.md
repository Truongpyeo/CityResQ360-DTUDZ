# Incident Service - Database Schema

## 📋 Thông tin chung

- **Service**: Incident Service
- **Port**: 8001
- **Database Type**: PostgreSQL 15
- **Database Name**: `incident_service_db`
- **Purpose**: Quản lý sự cố, cảnh báo, quy tắc cảnh báo tự động

---

## 📊 Danh sách bảng (4 bảng)

### 1. `su_cos` - Sự cố

**Mục đích**: Lưu trữ thông tin sự cố được tạo từ phản ánh

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `phan_anh_id` | BIGINT | Reference to core_api.phan_anhs.id (no FK) |
| `nguoi_bao_cao_id` | BIGINT | Reference to core_api.nguoi_dungs.id |
| `loai_su_co` | VARCHAR(100) | Loại sự cố |
| `muc_do_nghiem_trong` | SMALLINT | 0:low, 1:medium, 2:high, 3:critical |
| `trang_thai` | SMALLINT | 0:new, 1:monitoring, 2:alerted, 3:closed (default: 0) |
| `co_quan_phu_trach_id` | BIGINT | Reference to core_api.co_quan_xu_lys.id |
| `mo_ta` | TEXT | Mô tả chi tiết |
| `thoi_gian_xu_ly_du_kien` | TIMESTAMPTZ | Thời gian xử lý dự kiến |
| `thoi_gian_xu_ly_thuc_te` | TIMESTAMPTZ | Thời gian xử lý thực tế |
| `ghi_chu_xu_ly` | TEXT | Ghi chú xử lý |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |
| `deleted_at` | TIMESTAMPTZ | Soft delete |

**Indexes**:
- `idx_su_cos_phan_anh` on `phan_anh_id`
- `idx_su_cos_trang_thai` on `trang_thai`
- `idx_su_cos_muc_do` on `muc_do_nghiem_trong`
- `idx_su_cos_co_quan` on `co_quan_phu_trach_id`

---

### 2. `canh_baos` - Cảnh báo

**Mục đích**: Lưu trữ cảnh báo phát sinh từ sự cố

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `su_co_id` | BIGINT | Foreign key to su_cos.id |
| `ma_quy_tac` | VARCHAR(50) | Mã quy tắc cảnh báo |
| `loai_canh_bao` | SMALLINT | 0:sensor, 1:vision, 2:nlp, 3:manual |
| `thong_diep` | TEXT | Thông điệp cảnh báo |
| `thoi_gian_kich_hoat` | TIMESTAMPTZ | Thời gian kích hoạt |
| `thoi_gian_giai_quyet` | TIMESTAMPTZ | Thời gian giải quyết |
| `trang_thai` | SMALLINT | 0:active, 1:resolved (default: 0) |
| `muc_do_uu_tien` | SMALLINT | 0:info, 1:warning, 2:critical |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |

**Indexes**:
- `idx_canh_baos_su_co` on `su_co_id`
- `idx_canh_baos_trang_thai` on `trang_thai`
- `idx_canh_baos_muc_do` on `muc_do_uu_tien`

**Foreign Keys**:
- `su_co_id` → `su_cos(id)` ON DELETE CASCADE

---

### 3. `lich_su_trang_thai_su_cos` - Lịch sử trạng thái sự cố

**Mục đích**: Theo dõi lịch sử thay đổi trạng thái của sự cố

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `su_co_id` | BIGINT | Foreign key to su_cos.id |
| `trang_thai_cu` | SMALLINT | Trạng thái cũ |
| `trang_thai_moi` | SMALLINT | Trạng thái mới |
| `nguoi_thay_doi_id` | BIGINT | Reference to core_api.nguoi_dungs.id |
| `ghi_chu` | TEXT | Ghi chú thay đổi |
| `created_at` | TIMESTAMPTZ | Thời gian thay đổi |

**Indexes**:
- `idx_lich_su_su_co` on `su_co_id`
- `idx_lich_su_created` on `created_at`

**Foreign Keys**:
- `su_co_id` → `su_cos(id)` ON DELETE CASCADE

---

### 4. `quy_tac_canh_baos` - Quy tắc cảnh báo tự động

**Mục đích**: Định nghĩa các quy tắc để tự động tạo cảnh báo

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `ten_quy_tac` | VARCHAR(150) | Tên quy tắc |
| `ma_quy_tac` | VARCHAR(50) | Mã quy tắc (unique) |
| `mo_ta` | TEXT | Mô tả quy tắc |
| `dieu_kien` | JSONB | Điều kiện kích hoạt (JSON) |
| `hanh_dong` | JSONB | Hành động thực hiện (JSON) |
| `loai_quy_tac` | SMALLINT | 0:sensor, 1:time_based, 2:threshold, 3:pattern |
| `muc_do_uu_tien` | SMALLINT | Mức độ ưu tiên |
| `trang_thai` | SMALLINT | 1:active, 0:inactive (default: 1) |
| `so_lan_kich_hoat` | INTEGER | Số lần kích hoạt (default: 0) |
| `lan_kich_hoat_cuoi` | TIMESTAMPTZ | Lần kích hoạt cuối |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |
| `deleted_at` | TIMESTAMPTZ | Soft delete |

**Indexes**:
- `idx_quy_tac_ma` on `ma_quy_tac` (UNIQUE)
- `idx_quy_tac_trang_thai` on `trang_thai`
- `idx_quy_tac_loai` on `loai_quy_tac`

---

## 🔗 Quan hệ với các service khác

### Cross-service References (Application Level)
- `su_cos.phan_anh_id` → Core API: `phan_anhs.id`
- `su_cos.nguoi_bao_cao_id` → Core API: `nguoi_dungs.id`
- `su_cos.co_quan_phu_trach_id` → Core API: `co_quan_xu_lys.id`
- `lich_su_trang_thai_su_cos.nguoi_thay_doi_id` → Core API: `nguoi_dungs.id`

**Lưu ý**: Không sử dụng foreign key constraint cross-database. Đảm bảo referential integrity ở application layer.

---

## 📨 Event Integration

### Published Events
- `incident.created` - Khi tạo sự cố mới
- `incident.updated` - Khi cập nhật sự cố
- `incident.resolved` - Khi giải quyết sự cố
- `alert.triggered` - Khi cảnh báo được kích hoạt

### Consumed Events
- `reports.created` - Tạo sự cố từ phản ánh mới
- `reports.updated` - Cập nhật sự cố khi phản ánh thay đổi
- `sensor.observed` - Kiểm tra quy tắc cảnh báo từ dữ liệu sensor

---

## 🔧 Cấu hình Database

```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=incident_service_db
DB_USERNAME=incident_user
DB_PASSWORD=incident_password
DB_DRIVER=postgresql
```

---

## 📝 Notes

- Sử dụng PostgreSQL vì hỗ trợ JSONB cho quy tắc cảnh báo
- Indexes được tối ưu cho queries theo trạng thái và mức độ ưu tiên
- Soft delete (`deleted_at`) cho `su_cos` và `quy_tac_canh_baos`
- Timestamp dùng `TIMESTAMPTZ` để lưu timezone
