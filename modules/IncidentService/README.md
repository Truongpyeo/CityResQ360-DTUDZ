# IncidentService - CityResQ360-DTUDZ

Advanced incident workflow management and auto-dispatch service for emergency response system.

## 📋 Overview

IncidentService quản lý toàn bộ lifecycle của incidents từ khi được tạo ra từ reports cho đến khi được giải quyết. Service này cung cấp:

- ✅ **Workflow Management** - State machine để quản lý trạng thái incidents
- 🔄 **Auto-Dispatch** - Tự động phân công incidents cho agencies gần nhất (Day 2)
- 🔄 **SLA Monitoring** - Theo dõi và cảnh báo incidents quá hạn (Day 2)
- ✅ **Audit Trail** - Log đầy đủ mọi thay đổi trong workflow
- ✅ **Authentication** - JWT-based authentication từ CoreAPI
- ✅ **Authorization** - Role-based access control (CITIZEN, OFFICER, ADMIN)

## 🛠️ Tech Stack

- **Language:** Node.js 18+
- **Framework:** Express 4.x
- **Database:** PostgreSQL 16
- **ORM:** Sequelize 6.x
- **Authentication:** JWT (jsonwebtoken)
- **Validation:** express-validator
- **Logging:** Winston
- **Scheduler:** node-cron

## 📊 Database Schema

### Incidents Table
```sql
CREATE TABLE incidents (
  id SERIAL PRIMARY KEY,
  report_id INTEGER NOT NULL,
  assigned_agency_id INTEGER,
  assigned_user_id INTEGER,
  status VARCHAR(50) NOT NULL DEFAULT 'PENDING',
  priority VARCHAR(50) NOT NULL DEFAULT 'MEDIUM',
  due_date TIMESTAMP,
  assigned_at TIMESTAMP,
  resolved_at TIMESTAMP,
  closed_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);
```

### Workflow Logs Table
```sql
CREATE TABLE workflow_logs (
  id SERIAL PRIMARY KEY,
  incident_id INTEGER NOT NULL REFERENCES incidents(id),
  action VARCHAR(100) NOT NULL,
  from_status VARCHAR(50),
  to_status VARCHAR(50),
  notes TEXT,
  performed_by INTEGER,
  created_at TIMESTAMP DEFAULT NOW()
);
```

## 🚀 API Endpoints

### Authentication
All endpoints require JWT token in header:
```
Authorization: Bearer <token>
```

### 1. Create Incident
**POST** `/api/v1/incidents`

**Permissions:** All authenticated users

**Request:**
```json
{
  "report_id": 123,
  "priority": "HIGH",
  "assigned_agency_id": 5,
  "notes": "Urgent incident"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Incident created successfully",
  "data": {
    "id": 1,
    "report_id": 123,
    "status": "PENDING",
    "priority": "HIGH"
  }
}
```

### 2. List Incidents
**GET** `/api/v1/incidents?status=PENDING&priority=HIGH&page=1&limit=20`

**Permissions:** All authenticated users

**Query Parameters:**
- `status` - PENDING, IN_PROGRESS, RESOLVED, CLOSED
- `priority` - LOW, MEDIUM, HIGH, CRITICAL
- `assigned_agency_id` - Filter by agency
- `assigned_user_id` - Filter by user
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 20, max: 100)

### 3. Get Incident Details
**GET** `/api/v1/incidents/:id`

**Permissions:** All authenticated users

### 4. Assign Incident
**PUT** `/api/v1/incidents/:id/assign`

**Permissions:** OFFICER, ADMIN only

**Request:**
```json
{
  "assigned_agency_id": 5,
  "assigned_user_id": 10,
  "notes": "Assigned to Fire Department"
}
```

### 5. Update Status
**PUT** `/api/v1/incidents/:id/status`

**Permissions:** OFFICER, ADMIN only

**Request:**
```json
{
  "status": "RESOLVED",
  "notes": "Incident resolved successfully"
}
```

**Status Flow:**
```
PENDING → IN_PROGRESS → RESOLVED → CLOSED
```

## 🔐 Authentication & Authorization

### JWT Token Structure
Token từ CoreAPI phải chứa:
```json
{
  "user_id": 123,
  "email": "user@example.com",
  "role": "OFFICER",
  "exp": 1234567890
}
```

### Role Permissions
- **CITIZEN**: Create incidents, view all incidents
- **OFFICER**: All CITIZEN permissions + assign & update status
- **ADMIN**: Full access

## 🏃 Development

### Installation
```bash
npm install
cp .env.example .env
# Edit .env with your configuration
npm run dev
```

### Environment Variables
```bash
PORT=8005
NODE_ENV=development

POSTGRES_HOST=postgres_incident
POSTGRES_PORT=5434
POSTGRES_DB=incident_service_db
POSTGRES_USER=cityresq
POSTGRES_PASSWORD=cityresq_password

JWT_SECRET=your-jwt-secret-key
CORE_API_URL=http://core-api:8000

AUTO_DISPATCH_ENABLED=true
AUTO_DISPATCH_RADIUS_KM=10

SLA_CHECK_INTERVAL_MINUTES=5
SLA_DEFAULT_HOURS=24
```

## 🐳 Docker

### Build & Run
```bash
docker build -t incident-service .
docker run -p 8005:8005 --env-file .env incident-service
```

### Docker Compose
Service được orchestrated trong `infrastructure/docker/docker-compose.yml`

## 📊 Health Check

```bash
GET /health
```

**Response:**
```json
{
  "success": true,
  "service": "IncidentService",
  "version": "1.0.0",
  "status": "healthy",
  "database": { "status": "connected" },
  "statistics": {
    "total_incidents": 150,
    "pending": 20,
    "in_progress": 50
  }
}
```

## 🧪 Testing

```bash
npm test              # Run all tests
npm run test:coverage # With coverage report
```

## 📝 Implementation Progress

### ✅ Day 1 - Foundation (COMPLETED)
- [x] JWT authentication middleware
- [x] Request validation (express-validator)
- [x] Centralized error handling
- [x] Winston structured logging
- [x] Enhanced health check
- [x] Updated documentation

### 🔄 Day 2 - Business Logic (PENDING)
- [ ] Auto-dispatch algorithm
- [ ] SLA monitoring cron job
- [ ] Swagger documentation

### 🔄 Day 3 - Testing & Integration (PENDING)
- [ ] Unit tests (Jest)
- [ ] E2E tests (Supertest)
- [ ] Client library for CoreAPI
- [ ] DocsController integration

## 📄 License

GNU General Public License v3.0

Copyright (C) 2025 DTU-DZ Team
