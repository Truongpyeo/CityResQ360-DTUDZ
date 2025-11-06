# Analytics Service - Database Schema

## 📋 Thông tin chung

- **Service**: Analytics Service
- **Port**: 8009
- **Database Type**: ClickHouse 23.8 (OLAP Database)
- **Database Name**: `analytics_service_db`
- **Purpose**: Real-time analytics, dashboard metrics, aggregations

---

## 📊 Danh sách bảng (2 bảng)

### 1. `chi_so_dashboards` - Dashboard Metrics

**Mục đích**: Lưu trữ metrics cho dashboard (time-series aggregated data)

**Schema**:
```sql
CREATE TABLE analytics_service_db.chi_so_dashboards
(
    ngay Date COMMENT 'Ngày ghi nhận',
    gio DateTime COMMENT 'Giờ ghi nhận',
    khoa_chi_so String COMMENT 'Tên chỉ số',
    gia_tri_chi_so Decimal(15, 2) COMMENT 'Giá trị',
    don_vi String COMMENT 'Đơn vị (count, percent, hours, etc.)',
    danh_muc String COMMENT 'Danh mục (reports, incidents, response_time, city_points, agencies)',
    chu_ky String COMMENT 'Chu kỳ (hourly, daily, weekly, monthly)',
    du_lieu_mo_rong String COMMENT 'JSON metadata as String'
)
ENGINE = MergeTree()
PARTITION BY toYYYYMM(ngay)
ORDER BY (ngay, gio, khoa_chi_so, danh_muc)
TTL ngay + INTERVAL 365 DAY
SETTINGS index_granularity = 8192;
```

**Example Metrics**:
```sql
-- Total reports count
('2025-01-15', '2025-01-15 10:00:00', 'total_reports', 12345, 'count', 'reports', 'hourly', '{}')

-- Average response time
('2025-01-15', '2025-01-15 10:00:00', 'avg_response_time', 4.5, 'hours', 'incidents', 'hourly', '{}')

-- City points distributed today
('2025-01-15', '2025-01-15 10:00:00', 'city_points_distributed', 5000, 'points', 'city_points', 'daily', '{}')

-- Reports by category
('2025-01-15', '2025-01-15 10:00:00', 'reports_traffic', 345, 'count', 'reports', 'hourly', '{"category": "traffic"}')
('2025-01-15', '2025-01-15 10:00:00', 'reports_environment', 123, 'count', 'reports', 'hourly', '{"category": "environment"}')
```

**Common Queries**:
```sql
-- Get daily report count for last 30 days
SELECT 
    ngay,
    sum(gia_tri_chi_so) as total_reports
FROM chi_so_dashboards
WHERE khoa_chi_so = 'total_reports'
  AND ngay >= today() - 30
GROUP BY ngay
ORDER BY ngay;

-- Get reports by category (last 7 days)
SELECT 
    danh_muc,
    sum(gia_tri_chi_so) as total
FROM chi_so_dashboards
WHERE khoa_chi_so LIKE 'reports_%'
  AND ngay >= today() - 7
GROUP BY danh_muc
ORDER BY total DESC;

-- Get hourly metrics for today
SELECT 
    toHour(gio) as hour,
    khoa_chi_so,
    avg(gia_tri_chi_so) as avg_value
FROM chi_so_dashboards
WHERE ngay = today()
GROUP BY hour, khoa_chi_so
ORDER BY hour, khoa_chi_so;
```

---

### 2. `hieu_suat_co_quans` - Agency Performance

**Mục đích**: Lưu trữ metrics hiệu suất của các cơ quan xử lý

**Schema**:
```sql
CREATE TABLE analytics_service_db.hieu_suat_co_quans
(
    ngay Date COMMENT 'Ngày ghi nhận',
    co_quan_id UInt64 COMMENT 'ID cơ quan (reference to core_api.co_quan_xu_lys.id)',
    ten_co_quan String COMMENT 'Tên cơ quan',
    tong_phan_anh UInt32 COMMENT 'Tổng phản ánh nhận được',
    dang_xu_ly UInt32 COMMENT 'Đang xử lý',
    da_giai_quyet UInt32 COMMENT 'Đã giải quyết',
    bi_tu_choi UInt32 COMMENT 'Bị từ chối',
    trung_binh_thoi_gian_phan_hoi Float32 COMMENT 'Thời gian phản hồi TB (hours)',
    trung_binh_thoi_gian_giai_quyet Float32 COMMENT 'Thời gian giải quyết TB (hours)',
    ty_le_dung_han Float32 COMMENT 'Tỷ lệ giải quyết đúng hạn (%)',
    diem_hai_long Float32 COMMENT 'Điểm hài lòng trung bình (1-5)',
    ty_le_giai_quyet Float32 COMMENT 'Tỷ lệ giải quyết (%)',
    du_lieu_mo_rong String COMMENT 'JSON metadata'
)
ENGINE = ReplacingMergeTree(ngay)
PARTITION BY toYYYYMM(ngay)
ORDER BY (ngay, co_quan_id)
TTL ngay + INTERVAL 365 DAY
SETTINGS index_granularity = 8192;
```

**Common Queries**:
```sql
-- Get agency performance leaderboard (last 30 days)
SELECT 
    ten_co_quan,
    sum(tong_phan_anh) as total_reports,
    sum(da_giai_quyet) as resolved,
    avg(trung_binh_thoi_gian_giai_quyet) as avg_resolution_time,
    avg(diem_hai_long) as avg_satisfaction,
    avg(ty_le_giai_quyet) as resolution_rate
FROM hieu_suat_co_quans
WHERE ngay >= today() - 30
GROUP BY ten_co_quan
ORDER BY resolution_rate DESC, avg_resolution_time ASC;

-- Get agency performance trend
SELECT 
    ngay,
    ten_co_quan,
    tong_phan_anh,
    da_giai_quyet,
    trung_binh_thoi_gian_giai_quyet,
    diem_hai_long
FROM hieu_suat_co_quans
WHERE co_quan_id = 10
  AND ngay >= today() - 30
ORDER BY ngay;

-- Compare agencies by response time
SELECT 
    ten_co_quan,
    avg(trung_binh_thoi_gian_phan_hoi) as avg_response_time,
    avg(trung_binh_thoi_gian_giai_quyet) as avg_resolution_time
FROM hieu_suat_co_quans
WHERE ngay >= today() - 7
GROUP BY ten_co_quan
ORDER BY avg_response_time ASC;
```

---

## 📊 Example Dashboard Metrics

### Key Metrics
```sql
-- Total active reports
SELECT count(*) FROM core_api.phan_anhs WHERE trang_thai IN (0,1,2);

-- Reports created today
SELECT count(*) FROM core_api.phan_anhs WHERE DATE(created_at) = today();

-- Average response time (hours)
SELECT avg(thoi_gian_phan_hoi_thuc_te / 60.0) FROM core_api.phan_anhs WHERE thoi_gian_phan_hoi_thuc_te IS NOT NULL;

-- Resolution rate (%)
SELECT 
    (count(*) FILTER (WHERE trang_thai = 3) * 100.0) / count(*) as resolution_rate
FROM core_api.phan_anhs;
```

---

## 🔗 Quan hệ với các service khác

### Data Sources (via Events)
- Core API: Reports, Users, Agencies
- Incident Service: Incidents, Alerts
- Wallet Service: Transactions
- All services: Performance metrics

---

## 📨 Event Integration

### Consumed Events (for metrics calculation)
- `reports.created` - Tăng report count
- `reports.updated` - Cập nhật status metrics
- `reports.resolved` - Tính resolution time
- `incident.created` - Incident metrics
- `wallet.credited` - CityPoint distribution metrics
- `notification.sent` - Notification metrics

---

## 🔧 Cấu hình Database

```env
CLICKHOUSE_HOST=localhost
CLICKHOUSE_PORT=9000
CLICKHOUSE_HTTP_PORT=8123
CLICKHOUSE_DATABASE=analytics_service_db
CLICKHOUSE_USERNAME=analytics_user
CLICKHOUSE_PASSWORD=analytics_password

# Data retention
METRICS_RETENTION_DAYS=365
AGENCY_METRICS_RETENTION_DAYS=365

# Aggregation intervals
METRICS_HOURLY_AGGREGATION=true
METRICS_DAILY_AGGREGATION=true
METRICS_WEEKLY_AGGREGATION=true
METRICS_MONTHLY_AGGREGATION=true
```

---

## 📝 Notes

### Why ClickHouse?
- **OLAP database**: Tối ưu cho analytical queries
- **Columnar storage**: Fast aggregations
- **Real-time data ingestion**: Low latency
- **Compression**: Tiết kiệm storage
- **Scalability**: Horizontal scaling
- **TTL**: Auto-delete old data

### MergeTree Engine
- **MergeTree**: Standard engine cho time-series data
- **ReplacingMergeTree**: Deduplicate rows (dùng cho agency metrics)
- **Partitioning**: Partition theo tháng (YYYYMM)
- **ORDER BY**: Optimize for time-range queries
- **TTL**: Auto-delete data older than 365 days

### Data Pipeline
1. **Event consumers**: Consume events from Kafka/RabbitMQ
2. **Aggregation workers**: Calculate metrics (hourly/daily)
3. **Batch insert**: Insert metrics vào ClickHouse
4. **Materialized views**: Pre-calculate common aggregations
5. **Dashboard queries**: Fast queries từ aggregated data

### Optimization
- Use **materialized views** cho frequent queries
- **Partition pruning** với WHERE ngay conditions
- **Index granularity** = 8192 (default)
- **Compression codec**: LZ4 (fast) hoặc ZSTD (high compression)
- Cache frequent queries with **query_cache**

### Aggregation Examples
```sql
-- Materialized view for daily report count
CREATE MATERIALIZED VIEW daily_report_count_mv
ENGINE = SummingMergeTree()
PARTITION BY toYYYYMM(ngay)
ORDER BY ngay
AS SELECT 
    toDate(gio) as ngay,
    sum(gia_tri_chi_so) as total_reports
FROM chi_so_dashboards
WHERE khoa_chi_so = 'total_reports'
GROUP BY ngay;
```

---

## 🔍 Dashboard Use Cases

### 1. Executive Dashboard
- Total reports (today, this week, this month)
- Average response time
- Resolution rate
- CityPoints distributed
- Top agencies by performance

### 2. Category Analytics
- Reports by category (pie chart)
- Category trends over time (line chart)
- Top categories by volume
- Category resolution rates

### 3. Geospatial Heatmap
- Reports by location (from OpenSearch)
- Flood zones activity
- Agency coverage map

### 4. Agency Performance
- Leaderboard by resolution rate
- Response time comparison
- Satisfaction scores
- Workload distribution

### 5. Citizen Engagement
- Active users count
- Top contributors
- CityPoints leaderboard
- User growth trends

---

## 🛡️ Performance Considerations

- **Batch inserts**: Insert 1000+ rows at once
- **Async inserts**: Use async_insert = 1
- **Avoid SELECT ***: Select only needed columns
- **Use FINAL carefully**: Only when needed (slow)
- **Monitor query performance**: system.query_log table
- **Partition pruning**: Always filter by date
