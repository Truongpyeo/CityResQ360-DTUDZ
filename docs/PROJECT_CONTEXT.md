# 🏙️ CityResQ360 - Project Context & Architecture

> **Nền tảng phản ánh - cảnh báo - giám sát đô thị mở tích hợp AI**  
> Dự án tham gia OLP 2025 - Đề tài: Phần mềm nguồn mở

---

## 📋 **MỤC LỤC**

1. [Tổng quan đề tài](#1-tổng-quan-đề-tài)
2. [Kiến trúc hệ thống](#2-kiến-trúc-hệ-thống)
3. [Database Schema](#3-database-schema)
4. [Microservices Architecture](#4-microservices-architecture)
5. [Technology Stack](#5-technology-stack)
6. [API Contract](#6-api-contract)
7. [Development Roadmap](#7-development-roadmap)
8. [File Structure](#8-file-structure)

---

## **1. TỔNG QUAN ĐỀ TÀI**

### 🎯 **Đề tài OLP 2025**
**"Phát triển ứng dụng thành phố thông minh dựa trên nền tảng dữ liệu mở"**

### 🏆 **CityResQ360 - Đề tài 1**
**Nền tảng phản ánh - cảnh báo - giám sát đô thị mở tích hợp AI**

**Kết hợp 4 module:**
- ✅ **CivicPulse** - Phản ánh vấn đề từ người dân
- ✅ **FloodEye** - Giám sát ngập lụt
- ✅ **CityWallet** - Ví điện tử thành phố (token thưởng)
- ✅ **CityData Fabric** - Nền tảng dữ liệu mở

### 📱 **Yêu cầu kỹ thuật chính**

#### **1. Nền tảng**
- Web + Mobile cho thành phố thông minh
- Người dân, chính quyền, hệ thống AI cùng tham gia
- Giám sát, phân ánh, cảnh báo theo thời gian thực
- Dữ liệu mở (LOD + NGSI-LD)

#### **2. Công nghệ bắt buộc**

| Thành phần | Chi tiết |
|------------|----------|
| 🤖 **AI** | • NLP tiếng Việt (phân loại phản ánh)<br>• Computer Vision (nhận diện hình ảnh sự cố)<br>• Độ tin cậy (confidence score) |
| 🌐 **Open Data** | • OpenStreetMap (bản đồ)<br>• OpenWeather (thời tiết)<br>• GTFS (giao thông công cộng)<br>• Báo cáo từ công dân |
| 🔗 **Linked Data** | • Mô hình NGSI-LD<br>• SOSA/SSN ontology<br>• Event → Location → Agency → Status |
| 🪙 **Civic Token** | • CityPoint thưởng cho người phản ánh chính xác<br>• Khuyến khích tham gia |
| 📊 **Dashboard** | • Real-time map<br>• Thống kê tốc độ xử lý<br>• Phản hồi từ cơ quan<br>• Chỉ số minh bạch |

#### **3. Giải quyết các vấn đề**
- ✅ **Giao thông**: Kẹt xe, tai nạn, tình trạng đường
- ✅ **Môi trường**: Mất độ, chất lượng không khí, tiếng ồn
- ✅ **Dịch vụ công cộng**: Công viên, bãi đỗ xe, đèn đường
- ✅ **Hạ tầng**: Cấp/thoát nước, viễn thông, năng lượng
- ✅ **Ngập lụt**: Cảnh báo sớm, giám sát mực nước

---

## **2. KIẾN TRÚC HỆ THỐNG**

### 📐 **Sơ đồ tổng quan**

```
┌─────────────────────────────────────────────────────────────┐
│                    React Native App                          │
│                   (HTTPS/JWT + TPS)                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         API Gateway (Kong/Traefik/NGINX + TLS)              │
│         • Route/Policy                                       │
│         • Rate-limit                                         │
│         • OIDC/JWT Verify                                    │
└────────────────────────┬────────────────────────────────────┘
                         │
        ┌────────────────┴────────────────┐
        │                                  │
        ▼                                  ▼
┌──────────────────┐            ┌──────────────────────────┐
│  BFF/Public API  │            │   Microservices Layer    │
│    (Laravel)     │            │   (11 Services)          │
└──────────────────┘            └──────────────────────────┘
        │                                  │
        │                                  │
┌───────┴────────────────────────────────┴─────────────────┐
│           Laravel Reverb WebSocket Server                 │
│           • Real-time user notifications                  │
│           • Live dashboard updates                        │
│           • Frontend ↔ Backend duplex                     │
└────────────────────────┬──────────────────────────────────┘
                         │
┌────────────────────────┴──────────────────────────────────┐
│              Event Bus (Kafka/RabbitMQ)                   │
│              • reports.created                            │
│              • incident.updated                           │
│              • sensor.observed                            │
│              • notification.triggered                     │
│              • Backend-to-backend async                   │
└───────────────────────────────────────────────────────────┘
        │                                  │
        ▼                                  ▼
┌──────────────────┐            ┌──────────────────────────┐
│   Databases      │            │   External Services      │
│   (Multi-DB)     │            │   • MinIO/S3             │
│                  │            │   • OpenSearch           │
│                  │            │   • Redis                │
└──────────────────┘            └──────────────────────────┘
```

### 🎨 **Kiến trúc chi tiết**

#### **Frontend Layer**
- 📱 React Native Mobile App
- 🖥️ Admin Dashboard (Vue 3 - Optional)

#### **API Gateway Layer**
- Kong/Traefik/NGINX
- Authentication (Keycloak OIDC)
- Rate limiting
- TLS termination

#### **Backend Services Layer**

| # | Service | Tech Stack | Database | Port |
|---|---------|-----------|----------|------|
| 1 | **Core API** | Laravel 12 + PHP 8.4 | MySQL/PostgreSQL | 8000 |
| 2 | **Incident Service** | Go/Node.js | PostgreSQL | 8001 |
| 3 | **IoT Sensor Service** | Node.js + TypeScript | TimescaleDB | 8002 |
| 4 | **AI/ML Service** | Python + FastAPI | PostgreSQL + pgvector | 8003 |
| 5 | **Media Service** | Node.js | MongoDB + MinIO | 8004 |
| 6 | **Wallet Service** | Go | PostgreSQL | 8005 |
| 7 | **Notification Service** | Node.js | Redis + PostgreSQL | 8006 |
| 8 | **Search Service** | Python | OpenSearch | 8007 |
| 9 | **FloodEye Service** | Python + PostGIS | PostgreSQL | 8008 |
| 10 | **Analytics Service** | Python | ClickHouse | 8009 |
| 11 | **Context Broker** | Orion-LD/Scorpio | MongoDB | 1026 |

#### **Message Queue & Streaming**
- Kafka/RabbitMQ
- MQTT Broker (Mosquitto/EMQX)
- Redis Queue

#### **Data Storage**
- MySQL 8 / PostgreSQL 15
- TimescaleDB (Time-series)
- MongoDB (Document store)
- MinIO/S3 (Object storage)
- OpenSearch (Search engine)
- ClickHouse (OLAP analytics)
- Redis (Cache & Queue)

---

## **3. DATABASE SCHEMA**

### 📊 **Tổng quan Database**

**Tổng cộng: 34 bảng**
- Sử dụng **tiếng Việt** cho tên bảng và cột
- **Integer enum** với comment thay vì enum type
- Đầy đủ **indexes** và **foreign keys**
- Hỗ trợ **spatial data** (PostGIS)
- Hỗ trợ **time-series** (TimescaleDB)
- Hỗ trợ **vector search** (pgvector)

### 🗂️ **Phân bổ bảng theo Service**

#### **1. Core API Database (MySQL/PostgreSQL)**

```sql
✅ quan_tri_viens                 -- Quản trị viên
✅ nguoi_dungs                    -- Người dùng/Công dân
✅ co_quan_xu_lys                 -- Cơ quan xử lý
✅ phan_anhs                      -- Phản ánh (master table)
✅ binh_luan_phan_anhs            -- Bình luận
✅ binh_chon_phan_anhs            -- Bình chọn (upvote/downvote)
✅ nhat_ky_he_thongs              -- Audit logs
✅ cau_hinh_he_thongs             -- System config
✅ phien_ban_apis                 -- API versioning
```

**9 bảng** - User management, authentication, master data

---

#### **2. Incident Service Database (PostgreSQL)**

```sql
✅ su_cos                         -- Sự cố
✅ canh_baos                      -- Cảnh báo
✅ lich_su_trang_thai_su_cos      -- Lịch sử trạng thái
✅ quy_tac_canh_baos              -- Quy tắc cảnh báo tự động
```

**4 bảng** - Incident management, alerting, workflow

---

#### **3. IoT/Sensor Service Database (TimescaleDB)**

```sql
✅ cam_biens                      -- Cảm biến (master)
✅ quan_sats                      -- Quan sát (time-series hypertable)
✅ cam_bien_muc_nuocs             -- Cảm biến mực nước
```

**3 bảng** - Sensor metadata, time-series observations

---

#### **4. Media Service Database (MongoDB + MinIO)**

```javascript
✅ media_files (MongoDB)          -- File metadata
✅ File Storage (MinIO/S3)        -- Binary files
```

**1 collection** - File upload, storage, metadata

---

#### **5. Wallet Service Database (PostgreSQL)**

```sql
✅ vi_dien_tus                    -- Ví điện tử
✅ giao_dich_vi_dien_tus          -- Giao dịch CityPoint
✅ lich_su_city_points            -- Lịch sử token
```

**3 bảng** - CityWallet, token transactions, rewards

---

#### **6. FloodEye Service Database (PostgreSQL + PostGIS)**

```sql
✅ khu_vuc_ngap_luts              -- Khu vực ngập lụt
✅ canh_bao_ngap_luts             -- Cảnh báo ngập
✅ tuyen_giao_thongs              -- Tuyến giao thông (GTFS)
✅ tram_dungs                     -- Trạm dừng
✅ chi_tiet_tuyen_trams           -- Chi tiết tuyến-trạm
```

**5 bảng** - Flood monitoring, geospatial data, GTFS

---

#### **7. AI/ML Service Database (PostgreSQL + pgvector)**

```sql
✅ du_lieu_huan_luyen_ais         -- Training data
✅ hieu_suat_mo_hinhs             -- Model performance metrics
```

**2 bảng** - ML training, model versioning, embeddings

---

#### **8. Context Broker Database (MongoDB)**

```javascript
✅ ngsi_entities                  -- NGSI-LD entities
✅ entity_relationships           -- Entity relationships
✅ rdf_triples                    -- RDF triples (optional)
```

**3 collections** - NGSI-LD, Linked Data, semantic web

---

#### **9. Notification Service Database (Redis + PostgreSQL)**

```sql
✅ thong_baos (PostgreSQL)        -- Notification history
✅ Queue (Redis)                  -- Real-time queue
✅ Push tokens (Redis)            -- FCM tokens
```

**1 bảng + Redis** - Push notifications, email, SMS

---

#### **10. Analytics Service Database (ClickHouse)**

```sql
✅ chi_so_dashboards              -- Dashboard metrics
✅ hieu_suat_co_quans             -- Agency performance
```

**2 bảng** - Real-time analytics, aggregations

---

#### **11. Open Data Service Database (PostgreSQL)**

```sql
✅ nguon_du_lieu_mos              -- Open data sources
✅ bo_nho_dem_du_lieus            -- Data cache
✅ du_lieu_thoi_tiets             -- Weather data
```

**3 bảng** - OpenStreetMap, OpenWeather, GTFS integration

---

### 🔑 **Enum Mapping (Integer Comments)**

```sql
-- Ví dụ: vai_tro trong nguoi_dungs
vai_tro tinyint DEFAULT 0 COMMENT '0:citizen, 1:officer'

-- trang_thai trong phan_anhs  
trang_thai tinyint DEFAULT 0 COMMENT '0:pending, 1:verified, 2:in_progress, 3:resolved, 4:rejected'

-- danh_muc trong phan_anhs
danh_muc tinyint COMMENT '0:traffic, 1:environment, 2:fire, 3:waste, 4:flood, 5:other'

-- loai_giao_dich trong giao_dich_vi_dien_tus
loai_giao_dich tinyint COMMENT '0:reward, 1:spend, 2:admin_adjust'
```

**Lợi ích:**
- ✅ Performance tốt hơn enum string
- ✅ Dễ query và index
- ✅ Comment giúp developer hiểu
- ✅ Có thể thêm giá trị mới

---

## **4. MICROSERVICES ARCHITECTURE**

### 🎯 **Design Principles**

- **Database per Service**: Mỗi service có DB riêng
- **Event-Driven**: Kafka/RabbitMQ cho async communication
- **API Gateway**: Single entry point
- **Service Discovery**: Consul/Eureka
- **Circuit Breaker**: Resilience4j/Hystrix
- **Distributed Tracing**: Jaeger/Zipkin

### 🔄 **Communication Patterns**

#### **1. Synchronous (REST API)**
```http
POST http://incident-service:8001/api/incidents
Authorization: Bearer {token}
Content-Type: application/json

{
  "report_id": 12345,
  "severity": "high",
  "description": "..."
}
```

#### **2. Synchronous (gRPC)**
```protobuf
service IncidentService {
  rpc CreateIncident(CreateIncidentRequest) returns (IncidentResponse);
  rpc GetIncident(GetIncidentRequest) returns (IncidentResponse);
}
```

#### **3. Asynchronous (Event-Driven)**
```json
// Kafka Topic: reports.created
{
  "event_id": "uuid",
  "event_type": "ReportCreated",
  "timestamp": "2025-01-15T10:30:00Z",
  "data": {
    "report_id": 12345,
    "user_id": 789,
    "category": "traffic",
    "location": {
      "lat": 10.8231,
      "lon": 106.6297
    }
  }
}
```

### 📊 **Event Flow Example**

```
1. User gửi phản ánh qua Mobile App
   ↓
2. Core API validate & lưu vào phan_anhs
   ↓
3. Core API publish event: reports.created
   ↓
4. Consumers nhận event:
   ├─→ Incident Service: Tạo su_co
   ├─→ Search Service: Index vào OpenSearch
   ├─→ Notification Service: Gửi thông báo
   ├─→ AI Service: Phân loại & detect
   └─→ Analytics Service: Update metrics
```

---

## **5. TECHNOLOGY STACK**

### 🎨 **Frontend**

| Component | Technology |
|-----------|-----------|
| Mobile App | React Native 0.73 + TypeScript |
| State Management | Redux Toolkit + RTK Query |
| UI Library | React Native Paper |
| Maps | React Native Maps (Google/OSM) |
| Real-time | Socket.io client |
| Authentication | OAuth2 + JWT |

### ⚙️ **Backend**

#### **Core API (Laravel)**
```
- Laravel 12.37.0
- PHP 8.4.1
- Inertia.js 2.0.10 (optional admin)
- Laravel Fortify (2FA)
- Laravel Sanctum (API tokens)
- Laravel Horizon (queue)
- Laravel Reverb (WebSocket)
```

#### **Microservices**
```
- Go 1.21+ (Incident, Wallet)
- Node.js 20 LTS (IoT, Media, Notification)
- Python 3.11+ (AI/ML, Search, FloodEye, Analytics)
- FastAPI 0.110+
```

### 🗄️ **Databases**

| Database | Version | Use Case |
|----------|---------|----------|
| MySQL | 8.0 | Core relational data |
| PostgreSQL | 15 | Transactional services |
| TimescaleDB | 2.13 | Time-series IoT data |
| MongoDB | 7.0 | Document store, NGSI-LD |
| Redis | 7.2 | Cache, queue, sessions |
| OpenSearch | 2.11 | Full-text search |
| ClickHouse | 23.8 | OLAP analytics |
| MinIO | latest | Object storage |

### 🔄 **Message Queue & Streaming**

```
- Apache Kafka 3.6+
- RabbitMQ 3.12+ (alternative)
- Eclipse Mosquitto (MQTT)
```

### 🤖 **AI/ML Stack**

```python
# NLP
- transformers (PhoBERT, mBERT)
- vncorenlp (Vietnamese NLP)
- spacy
- scikit-learn

# Computer Vision
- YOLOv8 / YOLOv9
- TensorFlow / PyTorch
- OpenCV
- Pillow

# Vector Database
- pgvector (PostgreSQL extension)
- Faiss (optional)
```

### 🔍 **Search & Analytics**

```
- OpenSearch 2.11 (Elasticsearch fork)
- Kibana/OpenSearch Dashboards
- ClickHouse for OLAP
- Grafana for visualization
- Prometheus for metrics
```

### 🌐 **DevOps & Infrastructure**

```yaml
Containerization:
  - Docker 24+
  - Docker Compose 2.23+

Orchestration:
  - Kubernetes 1.28+ (production)
  - Helm charts

CI/CD:
  - GitHub Actions
  - GitLab CI/CD

Monitoring:
  - Prometheus + Grafana
  - ELK Stack (Elasticsearch, Logstash, Kibana)
  - Jaeger (distributed tracing)

Service Mesh:
  - Istio / Linkerd (optional)
```

---

## **6. API CONTRACT**

### 📝 **REST API Endpoints**

#### **Core API (Laravel) - Port 8000**

```yaml
# Authentication
POST   /api/auth/register          # Đăng ký
POST   /api/auth/login             # Đăng nhập
POST   /api/auth/logout            # Đăng xuất
POST   /api/auth/refresh           # Refresh token
GET    /api/auth/me                # User profile

# Reports (Phản ánh)
GET    /api/reports                # List reports
POST   /api/reports                # Create report
GET    /api/reports/{id}           # Get report detail
PUT    /api/reports/{id}           # Update report
DELETE /api/reports/{id}           # Delete report
POST   /api/reports/{id}/vote      # Vote (upvote/downvote)

# Comments
GET    /api/reports/{id}/comments  # List comments
POST   /api/reports/{id}/comments  # Add comment
PUT    /api/comments/{id}          # Update comment
DELETE /api/comments/{id}          # Delete comment

# Media
POST   /api/media/upload           # Upload file
GET    /api/media/{id}             # Get file
DELETE /api/media/{id}             # Delete file

# Users
GET    /api/users                  # List users (admin)
GET    /api/users/{id}             # Get user
PUT    /api/users/{id}             # Update user
GET    /api/users/{id}/reports     # User's reports

# Agencies
GET    /api/agencies               # List agencies
GET    /api/agencies/{id}          # Get agency
GET    /api/agencies/{id}/stats    # Agency statistics

# Wallet
GET    /api/wallet                 # Get wallet balance
GET    /api/wallet/transactions    # Transaction history
POST   /api/wallet/spend           # Spend points

# Dashboard
GET    /api/dashboard/stats        # Overall statistics
GET    /api/dashboard/map          # Map data
GET    /api/dashboard/chart        # Chart data
```

#### **Incident Service - Port 8001**

```yaml
GET    /api/v1/incidents                 # List incidents
POST   /api/v1/incidents                 # Create incident
GET    /api/v1/incidents/{id}            # Get incident
PUT    /api/v1/incidents/{id}            # Update incident
POST   /api/v1/incidents/{id}/assign     # Assign to agency
POST   /api/v1/incidents/{id}/resolve    # Resolve incident

GET    /api/v1/alerts                    # List alerts
POST   /api/v1/alerts                    # Create alert
GET    /api/v1/alerts/{id}               # Get alert
PUT    /api/v1/alerts/{id}/resolve       # Resolve alert

GET    /api/v1/rules                     # List alert rules
POST   /api/v1/rules                     # Create rule
PUT    /api/v1/rules/{id}                # Update rule
DELETE /api/v1/rules/{id}                # Delete rule
```

#### **IoT Service - Port 8002**

```yaml
GET    /api/v1/sensors                   # List sensors
POST   /api/v1/sensors                   # Register sensor
GET    /api/v1/sensors/{id}              # Get sensor
PUT    /api/v1/sensors/{id}              # Update sensor
DELETE /api/v1/sensors/{id}              # Delete sensor

POST   /api/v1/sensors/{id}/observations # Record observation
GET    /api/v1/sensors/{id}/observations # Get observations
GET    /api/v1/sensors/{id}/latest       # Get latest value

GET    /api/v1/observations              # Query observations
```

#### **AI/ML Service - Port 8003**

```yaml
POST   /api/v1/nlp/classify              # Classify Vietnamese text
POST   /api/v1/vision/detect             # Detect objects in image
POST   /api/v1/vision/analyze            # Analyze image content
POST   /api/v1/predict                   # General prediction

GET    /api/v1/models                    # List models
GET    /api/v1/models/{name}/metrics     # Model performance
POST   /api/v1/train/feedback            # Submit training feedback
```

### 🔌 **gRPC Services**

```protobuf
// proto/incident.proto
syntax = "proto3";

package incident;

service IncidentService {
  rpc CreateIncident(CreateIncidentRequest) returns (IncidentResponse);
  rpc GetIncident(GetIncidentRequest) returns (IncidentResponse);
  rpc UpdateIncident(UpdateIncidentRequest) returns (IncidentResponse);
  rpc ListIncidents(ListIncidentsRequest) returns (ListIncidentsResponse);
}

message CreateIncidentRequest {
  int64 report_id = 1;
  string type = 2;
  int32 severity = 3;
  string description = 4;
  int64 agency_id = 5;
}

message IncidentResponse {
  int64 id = 1;
  int64 report_id = 2;
  string type = 3;
  int32 severity = 4;
  int32 status = 5;
  string created_at = 6;
  string updated_at = 7;
}
```

### 📨 **Event Schemas (Kafka/RabbitMQ)**

#### **Event: reports.created**
```json
{
  "event_id": "550e8400-e29b-41d4-a716-446655440000",
  "event_type": "ReportCreated",
  "version": "1.0",
  "timestamp": "2025-01-15T10:30:00Z",
  "source": "core-api",
  "data": {
    "report_id": 12345,
    "user_id": 789,
    "title": "Đường bị ngập nặng",
    "description": "...",
    "category": 4,
    "priority": 2,
    "location": {
      "lat": 10.8231,
      "lon": 106.6297,
      "address": "123 Nguyễn Huệ, Q1, HCM"
    },
    "media": [
      {
        "id": 456,
        "url": "https://storage/images/xxx.jpg",
        "type": "image"
      }
    ]
  }
}
```

#### **Event: incident.updated**
```json
{
  "event_id": "uuid",
  "event_type": "IncidentUpdated",
  "version": "1.0",
  "timestamp": "2025-01-15T11:00:00Z",
  "source": "incident-service",
  "data": {
    "incident_id": 67890,
    "report_id": 12345,
    "old_status": 0,
    "new_status": 2,
    "severity": 2,
    "assigned_agency_id": 10,
    "updated_by": 5
  }
}
```

#### **Event: sensor.observed**
```json
{
  "event_id": "uuid",
  "event_type": "SensorObserved",
  "version": "1.0",
  "timestamp": "2025-01-15T10:45:00Z",
  "source": "iot-service",
  "data": {
    "sensor_id": 123,
    "sensor_code": "WL-HCM-001",
    "property": "waterLevel",
    "value": 85.5,
    "unit": "cm",
    "location": {
      "lat": 10.8231,
      "lon": 106.6297
    },
    "threshold_exceeded": true,
    "threshold_value": 80.0
  }
}
```

---

## **7. DEVELOPMENT ROADMAP**

### 📅 **Phase 1: Foundation (Tuần 1-2)**

#### **Week 1: Core Infrastructure**
- [x] Thiết kế database schema (34 bảng)
- [x] Phân bổ database theo microservices
- [ ] Setup development environment
  - [ ] Docker Compose với multi-database
  - [ ] Kafka/RabbitMQ
  - [ ] Redis, OpenSearch
- [ ] Core API (Laravel)
  - [ ] Authentication (Fortify + Sanctum)
  - [ ] User management
  - [ ] API versioning
  - [ ] Base controllers & models

#### **Week 2: Core Features**
- [ ] Core API (Laravel)
  - [ ] Reports module (CRUD)
  - [ ] Comments & Voting
  - [ ] File upload (integration với Media Service)
  - [ ] Event publishing (Kafka producer)
- [ ] Database migrations
  - [ ] Generate Laravel migrations từ schema
  - [ ] Seeders cho test data
- [ ] API documentation
  - [ ] OpenAPI/Swagger specs
  - [ ] Postman collections

---

### 📅 **Phase 2: Microservices (Tuần 3-4)**

#### **Week 3: Priority Services**
- [ ] **Incident Service** (Go/Node.js)
  - [ ] Incident CRUD
  - [ ] Alert management
  - [ ] Rule engine
  - [ ] Event consumers (Kafka)
- [ ] **IoT Service** (Node.js)
  - [ ] Sensor registration
  - [ ] MQTT broker integration
  - [ ] TimescaleDB setup
  - [ ] Observation recording
- [ ] **Media Service** (Node.js)
  - [ ] File upload to MinIO/S3
  - [ ] Image processing
  - [ ] Thumbnail generation
  - [ ] CDN integration

#### **Week 4: AI & Notification**
- [ ] **AI/ML Service** (Python FastAPI)
  - [ ] NLP model (PhoBERT) cho phân loại tiếng Việt
  - [ ] Computer Vision (YOLOv8) cho detect objects
  - [ ] API endpoints cho inference
  - [ ] Model versioning
- [ ] **Notification Service** (Node.js)
  - [ ] Push notification (FCM)
  - [ ] Email service (SMTP/SendGrid)
  - [ ] SMS service (Twilio)
  - [ ] WebSocket real-time
- [ ] **Wallet Service** (Go)
  - [ ] CityPoint balance management
  - [ ] Transaction processing
  - [ ] Reward calculation

---

### 📅 **Phase 3: Integration & Testing (Tuần 5-6)**

#### **Week 5: Integration**
- [ ] **Search Service** (OpenSearch)
  - [ ] Index reports & incidents
  - [ ] Full-text search
  - [ ] Geospatial queries
  - [ ] Aggregations
- [ ] **FloodEye Service** (Python PostGIS)
  - [ ] Flood zone management
  - [ ] Water level monitoring
  - [ ] Alert generation
  - [ ] OpenStreetMap integration
- [ ] **Context Broker** (Orion-LD)
  - [ ] NGSI-LD entity management
  - [ ] Linked Data queries
  - [ ] SOSA/SSN ontology
- [ ] **Analytics Service** (ClickHouse)
  - [ ] Dashboard metrics
  - [ ] Agency performance
  - [ ] Real-time aggregations

#### **Week 6: Testing & Polish**
- [ ] Integration testing
- [ ] Load testing (JMeter/K6)
- [ ] Security audit
- [ ] Performance optimization
- [ ] Documentation
- [ ] Demo preparation

---

### 📅 **Phase 4: Mobile App (Tuần 7-8)**

- [ ] React Native setup
- [ ] Authentication flow
- [ ] Report submission
- [ ] Map integration
- [ ] Real-time notifications
- [ ] Wallet & rewards
- [ ] User profile
- [ ] Testing & deployment

---

### 📅 **Phase 5: Deployment (Tuần 9-10)**

- [ ] Kubernetes manifests
- [ ] Helm charts
- [ ] CI/CD pipelines
- [ ] Monitoring setup (Prometheus + Grafana)
- [ ] Logging (ELK Stack)
- [ ] Backup strategy
- [ ] Production deployment
- [ ] Final testing

---

## **8. FILE STRUCTURE**

### 📁 **Repository Structure**

```
CityResQ360-Platform/
├── services/
│   ├── core-api/                    # Laravel - BFF/Gateway
│   │   ├── app/
│   │   │   ├── Http/Controllers/
│   │   │   ├── Models/
│   │   │   ├── Services/
│   │   │   └── Events/
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   └── seeders/
│   │   ├── routes/
│   │   ├── .env
│   │   ├── composer.json
│   │   └── DB.sql                  # Full schema reference
│   │
│   ├── incident-service/
│   │   ├── cmd/server/
│   │   ├── internal/
│   │   ├── pkg/
│   │   ├── migrations/
│   │   └── go.mod
│   │
│   ├── iot-sensor-service/
│   │   ├── src/
│   │   ├── config/
│   │   ├── migrations/
│   │   └── package.json
│   │
│   ├── ai-ml-service/
│   │   ├── app/
│   │   ├── models/
│   │   ├── requirements.txt
│   │   └── Dockerfile
│   │
│   ├── media-service/
│   ├── wallet-service/
│   ├── notification-service/
│   ├── search-service/
│   ├── floodeye-service/
│   ├── analytics-service/
│   └── context-broker/
│
├── infrastructure/
│   ├── docker/
│   │   ├── docker-compose.yml
│   │   ├── docker-compose.prod.yml
│   │   └── .env.example
│   ├── kubernetes/
│   │   ├── core-api/
│   │   ├── databases/
│   │   └── ingress/
│   ├── terraform/
│   └── nginx/
│
├── shared/
│   ├── proto/                      # gRPC definitions
│   │   ├── incident.proto
│   │   ├── notification.proto
│   │   └── user.proto
│   ├── events/                     # Event schemas
│   │   ├── reports.json
│   │   └── incidents.json
│   └── libraries/
│       ├── auth-lib/
│       └── logger-lib/
│
├── client/
│   ├── mobile-app/                 # React Native
│   │   ├── src/
│   │   ├── package.json
│   │   └── app.json
│   └── admin-dashboard/            # Vue 3 (Optional)
│       ├── src/
│       └── package.json
│
├── docs/
│   ├── api/
│   │   ├── openapi.yaml
│   │   └── postman/
│   ├── architecture/
│   │   ├── system-design.md
│   │   ├── database-schema.md
│   │   └── PROJECT_CONTEXT.md     # This file
│   ├── deployment/
│   └── guides/
│
├── scripts/
│   ├── setup.sh
│   ├── migrate-all.sh
│   ├── seed-data.sh
│   └── deploy.sh
│
├── .github/
│   └── workflows/
│       ├── core-api-ci.yml
│       ├── services-ci.yml
│       └── deploy.yml
│
├── docker-compose.yml              # Development
├── docker-compose.prod.yml         # Production
├── README.md
└── .gitignore
```

---

## **📚 THAM KHẢO**

### **Tài liệu đề thi**
- `Đề thi phần mềm nguồn mở - OLP 2025.pdf`
- Sơ đồ kiến trúc hệ thống

### **Database Schema**
- `/services/core-api/DB.sql` - Full 34 tables schema

### **Standards & Specifications**
- NGSI-LD: https://www.etsi.org/deliver/etsi_gs/CIM/001_099/009/01.08.01_60/gs_CIM009v010801p.pdf
- SOSA/SSN Ontology: https://www.w3.org/TR/vocab-ssn/
- OpenAPI 3.0: https://swagger.io/specification/
- gRPC: https://grpc.io/docs/
- GTFS: https://gtfs.org/

---

## **👥 TEAM & CONTACT**

**Project:** CityResQ360  
**Repository:** https://github.com/Truongpyeo/CityResQ360-DTUDZ  
**Branch:** develop  
**Competition:** OLP 2025 - Phần mềm nguồn mở  

**Tech Stack Summary:**
- Backend: Laravel 12, Go, Node.js, Python FastAPI
- Frontend: React Native, Vue 3
- Databases: MySQL, PostgreSQL, TimescaleDB, MongoDB, Redis, OpenSearch, ClickHouse
- Message Queue: Kafka/RabbitMQ
- IoT: MQTT (Mosquitto)
- AI/ML: PhoBERT, YOLOv8, TensorFlow/PyTorch
- DevOps: Docker, Kubernetes, GitHub Actions

---

## **📝 NOTES**

### **Important Decisions Made**

1. ✅ **Microservices Hybrid Approach**
   - Core API (Laravel) làm BFF layer
   - 11 microservices độc lập
   - Database per service
   - Event-driven communication

2. ✅ **Database Strategy**
   - Tiếng Việt cho tên bảng/cột
   - Integer enum với comment
   - Không dùng foreign key cross-database
   - Reference IDs giữa services

3. ✅ **Technology Choices**
   - TimescaleDB cho IoT time-series
   - MongoDB cho NGSI-LD (flexible schema)
   - ClickHouse cho analytics (OLAP)
   - pgvector cho ML embeddings
   - OpenSearch cho full-text search

4. ✅ **Communication Patterns**
   - REST API cho synchronous
   - gRPC cho high-performance
   - Kafka/RabbitMQ cho async events
   - WebSocket cho real-time

### **Next Actions**

- [ ] Generate Laravel migrations từ DB.sql
- [ ] Create OpenAPI specs cho tất cả services
- [ ] Setup docker-compose development environment
- [ ] Implement Core API authentication
- [ ] Create gRPC proto definitions
- [ ] Setup Kafka topics & schemas

---

**Last Updated:** January 15, 2025  
**Version:** 1.0.0  
**Status:** In Development 🚧
