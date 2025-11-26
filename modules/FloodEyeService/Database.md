# FloodEye Service - Database Schema

## 📋 Thông tin chung

- **Service**: FloodEye Service
- **Port**: 8008
- **Database Type**: PostgreSQL 15 + PostGIS Extension
- **Database Name**: `floodeye_service_db`
- **Purpose**: Giám sát ngập lụt, geospatial data, GTFS giao thông công cộng

---

## 📊 Danh sách bảng (5 bảng)

### 1. `khu_vuc_ngap_luts` - Khu vực ngập lụt

**Mục đích**: Lưu trữ thông tin khu vực có nguy cơ ngập lụt

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `ten_khu_vuc` | VARCHAR(150) | Tên khu vực |
| `ma_khu_vuc` | VARCHAR(50) | Mã khu vực (unique) |
| `vung_dia_ly` | GEOGRAPHY(POLYGON, 4326) | Vùng địa lý (PostGIS polygon) |
| `muc_do_rui_ro` | SMALLINT | 0:low, 1:medium, 2:high, 3:critical |
| `dan_so_anh_huong` | INTEGER | Dân số bị ảnh hưởng |
| `mo_ta` | TEXT | Mô tả chi tiết |
| `ngay_cap_nhat_rui_ro` | DATE | Ngày cập nhật đánh giá rủi ro |
| `trang_thai` | SMALLINT | 0:normal, 1:warning, 2:danger (default: 0) |
| `metadata` | JSONB | Metadata bổ sung |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |
| `deleted_at` | TIMESTAMPTZ | Soft delete |

**Indexes**:
- `idx_khu_vuc_ma` on `ma_khu_vuc` (UNIQUE)
- `idx_khu_vuc_muc_do` on `muc_do_rui_ro`
- `idx_khu_vuc_trang_thai` on `trang_thai`
- `idx_khu_vuc_geo` on `vung_dia_ly` USING GIST

**PostGIS Functions**:
```sql
-- Check if point is in flood zone
SELECT id, ten_khu_vuc
FROM khu_vuc_ngap_luts
WHERE ST_Contains(vung_dia_ly, ST_SetSRID(ST_MakePoint(106.6297, 10.8231), 4326));

-- Get nearby flood zones (within 2km)
SELECT id, ten_khu_vuc, ST_Distance(vung_dia_ly::geography, ST_SetSRID(ST_MakePoint(106.6297, 10.8231), 4326)::geography) AS distance
FROM khu_vuc_ngap_luts
WHERE ST_DWithin(vung_dia_ly::geography, ST_SetSRID(ST_MakePoint(106.6297, 10.8231), 4326)::geography, 2000)
ORDER BY distance;
```

---

### 2. `canh_bao_ngap_luts` - Cảnh báo ngập lụt

**Mục đích**: Lưu trữ cảnh báo ngập lụt theo thời gian thực

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `khu_vuc_id` | BIGINT | Foreign key to khu_vuc_ngap_luts.id |
| `muc_nuoc` | FLOAT | Mức nước đo được (cm) |
| `nguong_vuot_qua` | BOOLEAN | Có vượt ngưỡng không (default: false) |
| `thong_diep_canh_bao` | TEXT | Thông điệp cảnh báo |
| `thoi_gian_kich_hoat` | TIMESTAMPTZ | Thời gian kích hoạt cảnh báo |
| `thoi_gian_giai_quyet` | TIMESTAMPTZ | Thời gian giải quyết |
| `trang_thai` | SMALLINT | 0:active, 1:monitoring, 2:resolved (default: 0) |
| `muc_do_nghiem_trong` | SMALLINT | 0:info, 1:warning, 2:danger, 3:critical |
| `du_lieu_cam_bien` | JSONB | Dữ liệu từ cảm biến |
| `vi_tri_anh_huong` | GEOGRAPHY(POLYGON, 4326) | Vùng bị ảnh hưởng |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |

**Indexes**:
- `idx_canh_bao_khu_vuc` on `khu_vuc_id, created_at DESC`
- `idx_canh_bao_trang_thai` on `trang_thai`
- `idx_canh_bao_muc_do` on `muc_do_nghiem_trong`
- `idx_canh_bao_geo` on `vi_tri_anh_huong` USING GIST

**Foreign Keys**:
- `khu_vuc_id` → `khu_vuc_ngap_luts(id)` ON DELETE CASCADE

---

### 3. `tuyen_giao_thongs` - Tuyến giao thông công cộng (GTFS)

**Mục đích**: Lưu trữ thông tin tuyến giao thông công cộng

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `ma_tuyen` | VARCHAR(50) | Mã tuyến (unique) |
| `ten_tuyen` | VARCHAR(150) | Tên tuyến |
| `loai_phuong_tien` | SMALLINT | 0:bus, 1:metro, 2:train, 3:ferry |
| `tuyen_duong` | GEOGRAPHY(LINESTRING, 4326) | Tuyến đường (PostGIS linestring) |
| `mau_sac_tuyen` | VARCHAR(20) | Màu sắc hiển thị trên bản đồ |
| `trang_thai_hoat_dong` | SMALLINT | 1:active, 0:inactive (default: 1) |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |

**Indexes**:
- `idx_tuyen_ma` on `ma_tuyen` (UNIQUE)
- `idx_tuyen_loai` on `loai_phuong_tien`
- `idx_tuyen_geo` on `tuyen_duong` USING GIST

**PostGIS Functions**:
```sql
-- Find routes affected by flood zone
SELECT t.id, t.ten_tuyen
FROM tuyen_giao_thongs t
JOIN khu_vuc_ngap_luts k ON ST_Intersects(t.tuyen_duong, k.vung_dia_ly)
WHERE k.trang_thai = 2; -- danger
```

---

### 4. `tram_dungs` - Trạm dừng

**Mục đích**: Lưu trữ thông tin trạm dừng giao thông công cộng

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `ma_tram` | VARCHAR(50) | Mã trạm (unique) |
| `ten_tram` | VARCHAR(150) | Tên trạm |
| `vi_do` | DECIMAL(10,7) | Vĩ độ |
| `kinh_do` | DECIMAL(10,7) | Kinh độ |
| `dia_chi` | VARCHAR(255) | Địa chỉ |
| `loai_tram` | SMALLINT | 0:bus_stop, 1:metro_station, 2:train_station |
| `tien_nghi` | JSONB | Tiện nghi (shelter, bench, wifi, etc.) |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |

**Indexes**:
- `idx_tram_ma` on `ma_tram` (UNIQUE)
- `idx_tram_loai` on `loai_tram`
- `idx_tram_location` on `vi_do, kinh_do`

**PostGIS Functions**:
```sql
-- Create point geometry for spatial queries
CREATE INDEX idx_tram_geo ON tram_dungs USING GIST (ST_SetSRID(ST_MakePoint(kinh_do, vi_do), 4326));

-- Find stops in flood zone
SELECT s.id, s.ten_tram
FROM tram_dungs s
JOIN khu_vuc_ngap_luts k ON ST_Contains(k.vung_dia_ly, ST_SetSRID(ST_MakePoint(s.kinh_do, s.vi_do), 4326))
WHERE k.trang_thai = 2;
```

---

### 5. `chi_tiet_tuyen_trams` - Chi tiết tuyến - trạm

**Mục đích**: Many-to-many relationship giữa tuyến và trạm

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `tuyen_id` | BIGINT | Foreign key to tuyen_giao_thongs.id |
| `tram_id` | BIGINT | Foreign key to tram_dungs.id |
| `thu_tu_dung` | INTEGER | Thứ tự trạm trong tuyến |
| `khoang_cach_tu_tram_truoc` | FLOAT | Khoảng cách từ trạm trước (km) |
| `thoi_gian_du_kien` | INTEGER | Thời gian dự kiến đến trạm (minutes) |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |

**Indexes**:
- `idx_chi_tiet_tuyen` on `tuyen_id, thu_tu_dung`
- `idx_chi_tiet_tram` on `tram_id`

**Foreign Keys**:
- `tuyen_id` → `tuyen_giao_thongs(id)` ON DELETE CASCADE
- `tram_id` → `tram_dungs(id)` ON DELETE CASCADE

---

## 🔗 Quan hệ với các service khác

### Cross-service References (Application Level)
- IoT Service: `cam_bien_muc_nuocs.khu_vuc_ngap_lut_id` → `khu_vuc_ngap_luts.id`

---

## 📨 Event Integration

### Published Events
- `flood_zone.alert` - Cảnh báo ngập lụt mới
- `flood_zone.warning` - Mức nước vượt ngưỡng cảnh báo
- `flood_zone.critical` - Tình trạng ngập nghiêm trọng
- `route.affected` - Tuyến giao thông bị ảnh hưởng

### Consumed Events
- `sensor.observed` - Cập nhật mức nước từ cảm biến
- `weather.forecast` - Dự báo thời tiết (mưa lớn)
- `reports.flood` - Phản ánh ngập lụt từ người dân

---

## 🔧 Cấu hình Database

```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=floodeye_service_db
DB_USERNAME=floodeye_user
DB_PASSWORD=floodeye_password
DB_DRIVER=postgresql

# PostGIS Extension
POSTGIS_ENABLED=true
POSTGIS_VERSION=3.3

# Alert Thresholds
WATER_LEVEL_WARNING=50
WATER_LEVEL_DANGER=80
WATER_LEVEL_CRITICAL=120
```

---

## 📝 Notes

### PostGIS Features
- **GEOGRAPHY** type: Sử dụng cho spherical earth calculations (accurate distance)
- **POLYGON**: Lưu vùng ngập lụt
- **LINESTRING**: Lưu tuyến đường
- **POINT**: Lưu vị trí trạm dừng
- **SRID 4326**: WGS84 (GPS coordinates)

### Geospatial Queries
1. **ST_Contains**: Kiểm tra point có trong polygon không
2. **ST_Intersects**: Kiểm tra 2 geometry có giao nhau không
3. **ST_Distance**: Tính khoảng cách giữa 2 geometry
4. **ST_DWithin**: Tìm geometry trong bán kính nhất định
5. **ST_Buffer**: Tạo buffer zone xung quanh geometry

### GTFS Integration
- Import dữ liệu từ GTFS feeds (OpenStreetMap, local transport authority)
- Cập nhật định kỳ (daily/weekly)
- Route optimization khi có ngập lụt
- Alternative route suggestion

### Flood Alert Levels
- **Info** (0): Mức nước bình thường, chỉ theo dõi
- **Warning** (1): Mức nước cao, chuẩn bị ứng phó
- **Danger** (2): Ngập lụt đang xảy ra, cảnh báo khẩn
- **Critical** (3): Ngập nghiêm trọng, sơ tán

### Data Sources
- **Sensor data**: Real-time water level từ IoT sensors
- **Weather forecast**: OpenWeather API
- **Reports**: Phản ánh từ người dân
- **Historical data**: Dữ liệu lịch sử ngập lụt
- **OSM**: OpenStreetMap cho road network

---

## 🔍 Example Queries

### Find flood zones near a point
```sql
SELECT 
  id,
  ten_khu_vuc,
  muc_do_rui_ro,
  ST_Distance(vung_dia_ly::geography, ST_SetSRID(ST_MakePoint($1, $2), 4326)::geography) AS distance_meters
FROM khu_vuc_ngap_luts
WHERE ST_DWithin(vung_dia_ly::geography, ST_SetSRID(ST_MakePoint($1, $2), 4326)::geography, 5000)
ORDER BY distance_meters;
```

### Get active flood alerts with affected routes
```sql
SELECT 
  c.id AS alert_id,
  k.ten_khu_vuc,
  c.muc_nuoc,
  c.muc_do_nghiem_trong,
  COUNT(t.id) AS affected_routes
FROM canh_bao_ngap_luts c
JOIN khu_vuc_ngap_luts k ON c.khu_vuc_id = k.id
LEFT JOIN tuyen_giao_thongs t ON ST_Intersects(t.tuyen_duong, k.vung_dia_ly)
WHERE c.trang_thai = 0
GROUP BY c.id, k.ten_khu_vuc, c.muc_nuoc, c.muc_do_nghiem_trong;
```

### Get route with stops
```sql
SELECT 
  t.ten_tuyen,
  s.ten_tram,
  ct.thu_tu_dung,
  ct.khoang_cach_tu_tram_truoc,
  ct.thoi_gian_du_kien,
  s.vi_do,
  s.kinh_do
FROM tuyen_giao_thongs t
JOIN chi_tiet_tuyen_trams ct ON t.id = ct.tuyen_id
JOIN tram_dungs s ON ct.tram_id = s.id
WHERE t.ma_tuyen = $1
ORDER BY ct.thu_tu_dung;
```
