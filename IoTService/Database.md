# IoT Sensor Service - Database Schema

## 📋 Thông tin chung

- **Service**: IoT Sensor Service
- **Port**: 8002
- **Database Type**: TimescaleDB (PostgreSQL 15 + TimescaleDB Extension 2.13)
- **Database Name**: `iot_service_db`
- **Purpose**: Quản lý cảm biến IoT và dữ liệu time-series observations

---

## 📊 Danh sách bảng (3 bảng)

### 1. `cam_biens` - Cảm biến IoT

**Mục đích**: Lưu trữ thông tin metadata của cảm biến

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `ma_cam_bien` | VARCHAR(100) | Mã cảm biến (unique) |
| `ten_cam_bien` | VARCHAR(150) | Tên cảm biến |
| `loai_cam_bien` | VARCHAR(100) | Loại cảm biến (water_level, temperature, etc.) |
| `vi_do` | DECIMAL(10,7) | Vĩ độ |
| `kinh_do` | DECIMAL(10,7) | Kinh độ |
| `gia_tri_cuoi` | FLOAT | Giá trị đo cuối cùng |
| `don_vi` | VARCHAR(50) | Đơn vị đo (cm, °C, etc.) |
| `nha_san_xuat` | VARCHAR(100) | Nhà sản xuất |
| `mo_hinh` | VARCHAR(100) | Mô hình |
| `so_seri` | VARCHAR(150) | Số serial |
| `ngay_lap_dat` | DATE | Ngày lắp đặt |
| `ngay_bao_tri_cuoi` | DATE | Ngày bảo trì cuối |
| `muc_pin` | FLOAT | Mức pin (%) |
| `cuong_do_tin_hieu` | INTEGER | Cường độ tín hiệu (dBm) |
| `trang_thai_truc_tuyen` | BOOLEAN | Trạng thái online (default: true) |
| `trang_thai_hieu_chuan` | SMALLINT | 0:calibrated, 1:needs_calibration, 2:faulty |
| `du_lieu_mo_rong` | JSONB | NGSI-LD metadata |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |
| `deleted_at` | TIMESTAMPTZ | Soft delete |

**Indexes**:
- `idx_cam_biens_ma` on `ma_cam_bien` (UNIQUE)
- `idx_cam_biens_loai` on `loai_cam_bien`
- `idx_cam_biens_trang_thai` on `trang_thai_truc_tuyen`
- `idx_cam_biens_location` on `vi_do, kinh_do` (for geospatial queries)

---

### 2. `quan_sats` - Quan sát (Time-series Hypertable)

**Mục đích**: Lưu trữ dữ liệu quan sát time-series từ cảm biến

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `time` | TIMESTAMPTZ | Thời gian đo (PRIMARY, partitioned) |
| `cam_bien_id` | BIGINT | Reference to cam_biens.id |
| `thuoc_tinh_quan_sat` | VARCHAR(100) | Thuộc tính đo (waterLevel, temperature, etc.) |
| `gia_tri` | DOUBLE PRECISION | Giá trị đo |
| `don_vi` | VARCHAR(50) | Đơn vị |
| `chat_luong_du_lieu` | SMALLINT | 0:good, 1:fair, 2:poor |
| `ghi_chu` | TEXT | Ghi chú |
| `created_at` | TIMESTAMPTZ | Default: now() |

**TimescaleDB Configuration**:
```sql
-- Convert to hypertable
SELECT create_hypertable('quan_sats', 'time');

-- Compression policy (data older than 7 days)
SELECT add_compression_policy('quan_sats', INTERVAL '7 days');

-- Retention policy (delete data older than 1 year)
SELECT add_retention_policy('quan_sats', INTERVAL '1 year');
```

**Indexes**:
- `idx_quan_sats_cam_bien` on `cam_bien_id, time DESC`
- `idx_quan_sats_thuoc_tinh` on `thuoc_tinh_quan_sat, time DESC`

---

### 3. `cam_bien_muc_nuocs` - Cảm biến mực nước (cho FloodEye)

**Mục đích**: Lưu trữ cấu hình và trạng thái cảm biến mực nước

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `cam_bien_id` | BIGINT | Reference to cam_biens.id |
| `khu_vuc_ngap_lut_id` | BIGINT | Reference to floodeye_service.khu_vuc_ngap_luts.id |
| `muc_nuoc_hien_tai` | FLOAT | Mức nước hiện tại (cm) |
| `nguong_canh_bao` | FLOAT | Ngưỡng cảnh báo (cm) |
| `nguong_nguy_hiem` | FLOAT | Ngưỡng nguy hiểm (cm) |
| `thoi_gian_do_cuoi` | TIMESTAMPTZ | Thời gian đo cuối |
| `trang_thai_hoat_dong` | SMALLINT | 0:normal, 1:warning, 2:error |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |

**Indexes**:
- `idx_cam_bien_muc_nuoc_cam_bien` on `cam_bien_id`
- `idx_cam_bien_muc_nuoc_khu_vuc` on `khu_vuc_ngap_lut_id`
- `idx_cam_bien_muc_nuoc_trang_thai` on `trang_thai_hoat_dong`

---

## 🔗 Quan hệ với các service khác

### Cross-service References (Application Level)
- `cam_bien_muc_nuocs.khu_vuc_ngap_lut_id` → FloodEye Service: `khu_vuc_ngap_luts.id`

---

## 📨 Event Integration

### Published Events
- `sensor.registered` - Khi đăng ký cảm biến mới
- `sensor.observed` - Khi có dữ liệu quan sát mới
- `sensor.threshold_exceeded` - Khi giá trị vượt ngưỡng
- `sensor.offline` - Khi cảm biến mất kết nối

### Consumed Events
- `flood_zone.created` - Cập nhật cảm biến mực nước với khu vực mới

---

## 🔧 Cấu hình Database

```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=iot_service_db
DB_USERNAME=iot_user
DB_PASSWORD=iot_password
DB_DRIVER=postgresql

# TimescaleDB Extension
TIMESCALEDB_ENABLED=true
TIMESCALEDB_COMPRESSION=true
TIMESCALEDB_RETENTION_DAYS=365
```

---

## 📝 Notes

- **TimescaleDB** được sử dụng cho time-series data optimization
- Bảng `quan_sats` là **hypertable** với automatic partitioning theo thời gian
- **Compression policy**: Tự động nén dữ liệu cũ hơn 7 ngày
- **Retention policy**: Tự động xóa dữ liệu cũ hơn 1 năm
- Indexes được tối ưu cho queries theo thời gian và cảm biến
- MQTT Broker (Mosquitto) được sử dụng để nhận dữ liệu real-time từ IoT devices
- Hỗ trợ NGSI-LD metadata trong `du_lieu_mo_rong` (JSONB)

---

## 🌐 MQTT Topics

```
sensors/{sensor_code}/data     - Dữ liệu quan sát
sensors/{sensor_code}/status   - Trạng thái cảm biến
sensors/{sensor_code}/config   - Cấu hình cảm biến
sensors/+/alert                - Cảnh báo từ bất kỳ cảm biến nào
```
