# 🚀 CityResQ360 - Development Workflow

> Quy trình code chi tiết cho từng service - OLP 2025

---

## 📋 Mục lục

1. [Thứ tự triển khai services](#thứ-tự-triển-khai-services)
2. [Quy trình code chuẩn](#quy-trình-code-chuẩn)
3. [Chi tiết từng phase](#chi-tiết-từng-phase)
4. [Checklist cho mỗi service](#checklist-cho-mỗi-service)
5. [Best practices](#best-practices)

---

## 🎯 Thứ tự triển khai services

### **Dependency Graph**

```
Core API (Foundation)
    ↓
    ├─→ Media Service (file storage)
    ├─→ Notification Service (alerts)
    ├─→ Wallet Service (rewards)
    │
    ↓
Incident Service (business logic)
    ↓
    ├─→ AI/ML Service (classification)
    ├─→ IoT Service (sensors)
    │
    ↓
FloodEye Service (specialized)
    ↓
    ├─→ Search Service (indexing)
    ├─→ Analytics Service (metrics)
    │
    ↓
Context Broker (optional)
```

### **Phase 1: Foundation (Tuần 1-2) - BẮT ĐẦU ĐÂY**

#### ✅ **1. Core API** (Laravel - Port 8000)
**Ưu tiên cao nhất** - Là service trung tâm

**Lý do:**
- Master data (users, agencies, reports)
- Authentication gateway
- Entry point cho mobile app
- Các service khác reference data từ đây

**Timeline:** 5-7 ngày

---

### **Phase 2: Support Services (Tuần 2-3)**

#### ✅ **2. Media Service** (Node.js - Port 8004)
**Độc lập** - Không phụ thuộc service khác

**Lý do:**
- Core API và AI/ML cần upload files
- Có thể triển khai song song với Core API

**Timeline:** 2-3 ngày

#### ✅ **3. Notification Service** (Node.js - Port 8006)
**Độc lập** - Chỉ consume events

**Lý do:**
- Tất cả services cần gửi notifications
- Có thể test ngay với Core API events

**Timeline:** 2-3 ngày

#### ✅ **4. Wallet Service** (Go - Port 8005)
**Phụ thuộc:** Core API (users)

**Lý do:**
- Cần users từ Core API
- Reward system cho reports

**Timeline:** 3-4 ngày

---

### **Phase 3: Core Business (Tuần 3-4)**

#### ✅ **5. Incident Service** (Go/Node.js - Port 8001)
**Phụ thuộc:** Core API

**Lý do:**
- Xử lý sự cố từ reports
- Core của business logic

**Timeline:** 4-5 ngày

#### ✅ **6. AI/ML Service** (Python FastAPI - Port 8003)
**Phụ thuộc:** Core API, Media Service

**Lý do:**
- Phân tích reports và images
- Cần data từ Core API và Media

**Timeline:** 5-7 ngày (train models)

---

### **Phase 4: Specialized (Tuần 4-5)**

#### ✅ **7. IoT Service** (Node.js - Port 8002)
**Có thể song song với AI/ML**

**Timeline:** 3-4 ngày

#### ✅ **8. FloodEye Service** (Python - Port 8008)
**Phụ thuộc:** IoT Service

**Timeline:** 3-4 ngày

---

### **Phase 5: Advanced (Tuần 5-6)**

#### ✅ **9. Search Service** (Python - Port 8007)
**Phụ thuộc:** Tất cả services có data

**Timeline:** 2-3 ngày

#### ✅ **10. Analytics Service** (Python - Port 8009)
**Phụ thuộc:** Tất cả services

**Timeline:** 3-4 ngày

#### ⚠️ **11. Context Broker** (Orion-LD - Port 1026)
**Optional** - Triển khai nếu còn thời gian

**Timeline:** 2-3 ngày

---

## 🔧 Quy trình code chuẩn cho mỗi service

### **Template workflow (áp dụng cho mọi service):**

```bash
# Bước 1: Tạo project structure
# Bước 2: Setup database & migrations
# Bước 3: Implement API endpoints
# Bước 4: Event integration (publish/consume)
# Bước 5: Tests
# Bước 6: Docker & deployment
```

---

## 📦 Chi tiết Phase 1: Core API (BẮT ĐẦU ĐÂY)

### **Day 1: Infrastructure Setup**

#### 1.1. Docker Compose Stack
```bash
cd /Volumes/MyVolume/Laravel/CityResQ360-DTUDZ

# Tạo infrastructure folder
mkdir -p infrastructure/docker
```

**File: `infrastructure/docker/docker-compose.dev.yml`**
```yaml
version: '3.8'

services:
  # PostgreSQL - Main database
  postgres:
    image: postgres:15-alpine
    container_name: cityresq-postgres
    ports:
      - "5432:5432"
    environment:
      POSTGRES_USER: cityresq_user
      POSTGRES_PASSWORD: cityresq_password
      POSTGRES_DB: core_api_db
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./init-databases.sql:/docker-entrypoint-initdb.d/init.sql
    networks:
      - cityresq-network

  # Redis - Cache & Queue
  redis:
    image: redis:7-alpine
    container_name: cityresq-redis
    ports:
      - "6379:6379"
    networks:
      - cityresq-network

  # RabbitMQ - Message broker
  rabbitmq:
    image: rabbitmq:3-management-alpine
    container_name: cityresq-rabbitmq
    ports:
      - "5672:5672"   # AMQP
      - "15672:15672" # Management UI
    environment:
      RABBITMQ_DEFAULT_USER: cityresq
      RABBITMQ_DEFAULT_PASS: cityresq_password
    networks:
      - cityresq-network

  # MinIO - Object storage
  minio:
    image: minio/minio:latest
    container_name: cityresq-minio
    ports:
      - "9000:9000"
      - "9001:9001"
    environment:
      MINIO_ROOT_USER: minioadmin
      MINIO_ROOT_PASSWORD: minioadmin
    command: server /data --console-address ":9001"
    volumes:
      - minio_data:/data
    networks:
      - cityresq-network

  # MongoDB - Document store
  mongodb:
    image: mongo:7
    container_name: cityresq-mongodb
    ports:
      - "27017:27017"
    environment:
      MONGO_INITDB_ROOT_USERNAME: cityresq
      MONGO_INITDB_ROOT_PASSWORD: cityresq_password
    volumes:
      - mongodb_data:/data/db
    networks:
      - cityresq-network

volumes:
  postgres_data:
  minio_data:
  mongodb_data:

networks:
  cityresq-network:
    driver: bridge
```

**File: `infrastructure/docker/init-databases.sql`**
```sql
-- Create databases for all services
CREATE DATABASE core_api_db;
CREATE DATABASE incident_service_db;
CREATE DATABASE iot_service_db;
CREATE DATABASE aiml_service_db;
CREATE DATABASE wallet_service_db;
CREATE DATABASE notification_service_db;
CREATE DATABASE floodeye_service_db;

-- Create users
CREATE USER core_api_user WITH PASSWORD 'core_api_password';
CREATE USER incident_user WITH PASSWORD 'incident_password';
CREATE USER iot_user WITH PASSWORD 'iot_password';
CREATE USER aiml_user WITH PASSWORD 'aiml_password';
CREATE USER wallet_user WITH PASSWORD 'wallet_password';
CREATE USER notification_user WITH PASSWORD 'notification_password';
CREATE USER floodeye_user WITH PASSWORD 'floodeye_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE core_api_db TO core_api_user;
GRANT ALL PRIVILEGES ON DATABASE incident_service_db TO incident_user;
GRANT ALL PRIVILEGES ON DATABASE iot_service_db TO iot_user;
GRANT ALL PRIVILEGES ON DATABASE aiml_service_db TO aiml_user;
GRANT ALL PRIVILEGES ON DATABASE wallet_service_db TO wallet_user;
GRANT ALL PRIVILEGES ON DATABASE notification_service_db TO notification_user;
GRANT ALL PRIVILEGES ON DATABASE floodeye_service_db TO floodeye_user;
```

**Chạy Docker Compose:**
```bash
cd infrastructure/docker
docker-compose -f docker-compose.dev.yml up -d

# Verify
docker ps
docker logs cityresq-postgres
```

---

#### 1.2. Core API - Database Setup

**Update `CoreAPI/.env`:**
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=core_api_db
DB_USERNAME=core_api_user
DB_PASSWORD=core_api_password

REDIS_HOST=localhost
REDIS_PASSWORD=null
REDIS_PORT=6379

# RabbitMQ
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=cityresq
RABBITMQ_PASSWORD=cityresq_password
RABBITMQ_VHOST=/

# MinIO
MINIO_ENDPOINT=localhost:9000
MINIO_KEY=minioadmin
MINIO_SECRET=minioadmin
MINIO_BUCKET=cityresq-media
MINIO_USE_SSL=false
```

---

### **Day 2-3: Database Migrations**

#### 2.1. Generate Migrations

```bash
cd CoreAPI

# Migration 1: quan_tri_viens
php artisan make:migration create_quan_tri_viens_table

# Migration 2: nguoi_dungs
php artisan make:migration create_nguoi_dungs_table

# Migration 3: co_quan_xu_lys
php artisan make:migration create_co_quan_xu_lys_table

# Migration 4: phan_anhs
php artisan make:migration create_phan_anhs_table

# Migration 5: binh_luan_phan_anhs
php artisan make:migration create_binh_luan_phan_anhs_table

# Migration 6: binh_chon_phan_anhs
php artisan make:migration create_binh_chon_phan_anhs_table

# Migration 7: nhat_ky_he_thongs
php artisan make:migration create_nhat_ky_he_thongs_table

# Migration 8: cau_hinh_he_thongs
php artisan make:migration create_cau_hinh_he_thongs_table

# Migration 9: phien_ban_apis
php artisan make:migration create_phien_ban_apis_table
```

**Example Migration: `database/migrations/xxxx_create_nguoi_dungs_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nguoi_dungs', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten', 120);
            $table->string('email', 190)->unique();
            $table->string('mat_khau', 255);
            $table->string('so_dien_thoai', 20)->nullable();
            $table->tinyInteger('vai_tro')->default(0)->comment('0:citizen, 1:officer');
            $table->string('anh_dai_dien', 255)->nullable();
            $table->tinyInteger('trang_thai')->default(1)->comment('1:active, 0:banned');
            $table->integer('diem_thanh_pho')->default(0)->comment('CityPoint token');
            $table->boolean('xac_thuc_cong_dan')->default(false)->comment('KYC verified');
            $table->integer('diem_uy_tin')->default(0);
            $table->integer('tong_so_phan_anh')->default(0);
            $table->integer('so_phan_anh_chinh_xac')->default(0);
            $table->float('ty_le_chinh_xac')->default(0)->comment('%');
            $table->tinyInteger('cap_huy_hieu')->default(0)->comment('0:bronze, 1:silver, 2:gold, 3:platinum');
            $table->string('push_token', 255)->nullable()->comment('FCM token');
            $table->json('tuy_chon_thong_bao')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('so_dien_thoai');
            $table->index(['vai_tro', 'trang_thai']);
            $table->index('diem_thanh_pho');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nguoi_dungs');
    }
};
```

**Run migrations:**
```bash
php artisan migrate
```

---

#### 2.2. Seeders

```bash
php artisan make:seeder AdminSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder AgencySeeder
php artisan make:seeder ReportSeeder
```

**Example: `database/seeders/UserSeeder.php`**
```php
<?php

namespace Database\Seeders;

use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users
        NguoiDung::create([
            'ho_ten' => 'Nguyễn Văn A',
            'email' => 'user1@example.com',
            'mat_khau' => Hash::make('password'),
            'so_dien_thoai' => '0901234567',
            'vai_tro' => 0, // citizen
            'diem_thanh_pho' => 100,
        ]);

        // Generate more test users
        NguoiDung::factory(50)->create();
    }
}
```

**Run seeders:**
```bash
php artisan db:seed
```

---

### **Day 4: Authentication (Fortify + Sanctum)**

#### 3.1. Install & Configure

```bash
cd CoreAPI
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Update `config/fortify.php`:**
```php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]),
],
```

#### 3.2. Auth API Routes

**File: `routes/api.php`**
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});
```

---

### **Day 5-6: Reports CRUD**

#### 4.1. Models

```bash
php artisan make:model PhanAnh
php artisan make:model CoQuanXuLy
php artisan make:model BinhLuanPhanAnh
php artisan make:model BinhChonPhanAnh
```

#### 4.2. Controllers

```bash
php artisan make:controller Api/ReportController --api
php artisan make:controller Api/CommentController --api
php artisan make:controller Api/VoteController
```

#### 4.3. Requests (Validation)

```bash
php artisan make:request StoreReportRequest
php artisan make:request UpdateReportRequest
```

**Example: `app/Http/Requests/StoreReportRequest.php`**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'required|string',
            'danh_muc' => 'required|integer|between:0,5',
            'uu_tien' => 'nullable|integer|between:0,3',
            'vi_do' => 'required|numeric|between:-90,90',
            'kinh_do' => 'required|numeric|between:-180,180',
            'dia_chi' => 'nullable|string|max:255',
            'la_cong_khai' => 'boolean',
            'the_tags' => 'nullable|array',
        ];
    }
}
```

#### 4.4. Resources (API Response)

```bash
php artisan make:resource ReportResource
php artisan make:resource ReportCollection
```

#### 4.5. Routes

**File: `routes/api.php`**
```php
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\VoteController;

// Reports
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('reports', ReportController::class);
    Route::post('reports/{report}/vote', [VoteController::class, 'vote']);
    
    // Comments
    Route::get('reports/{report}/comments', [CommentController::class, 'index']);
    Route::post('reports/{report}/comments', [CommentController::class, 'store']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
});
```

---

### **Day 7: Event Publishing (RabbitMQ)**

#### 5.1. Install RabbitMQ package

```bash
composer require vladimir-yuldashev/laravel-queue-rabbitmq
```

**Update `config/queue.php`:**
```php
'connections' => [
    'rabbitmq' => [
        'driver' => 'rabbitmq',
        'queue' => env('RABBITMQ_QUEUE', 'default'),
        'connection' => PhpAmqpLib\Connection\AMQPLazyConnection::class,
        'hosts' => [
            [
                'host' => env('RABBITMQ_HOST', '127.0.0.1'),
                'port' => env('RABBITMQ_PORT', 5672),
                'user' => env('RABBITMQ_USER', 'guest'),
                'password' => env('RABBITMQ_PASSWORD', 'guest'),
                'vhost' => env('RABBITMQ_VHOST', '/'),
            ],
        ],
    ],
],
```

#### 5.2. Create Events

```bash
php artisan make:event ReportCreated
php artisan make:event ReportUpdated
```

**Example: `app/Events/ReportCreated.php`**
```php
<?php

namespace App\Events;

use App\Models\PhanAnh;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PhanAnh $report)
    {
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
```

#### 5.3. Event Listeners (Publish to RabbitMQ)

```bash
php artisan make:listener PublishReportCreatedEvent
```

**Example: `app/Listeners/PublishReportCreatedEvent.php`**
```php
<?php

namespace App\Listeners;

use App\Events\ReportCreated;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PublishReportCreatedEvent
{
    public function handle(ReportCreated $event): void
    {
        $connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.hosts.0.host'),
            config('queue.connections.rabbitmq.hosts.0.port'),
            config('queue.connections.rabbitmq.hosts.0.user'),
            config('queue.connections.rabbitmq.hosts.0.password')
        );

        $channel = $connection->channel();
        $channel->exchange_declare('cityresq.events', 'topic', false, true, false);

        $message = json_encode([
            'event_id' => \Illuminate\Support\Str::uuid(),
            'event_type' => 'ReportCreated',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'report_id' => $event->report->id,
                'user_id' => $event->report->nguoi_dung_id,
                'title' => $event->report->tieu_de,
                'category' => $event->report->danh_muc,
                'location' => [
                    'lat' => $event->report->vi_do,
                    'lon' => $event->report->kinh_do,
                ],
            ],
        ]);

        $msg = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $channel->basic_publish($msg, 'cityresq.events', 'reports.created');

        $channel->close();
        $connection->close();
    }
}
```

**Register in `app/Providers/EventServiceProvider.php`:**
```php
protected $listen = [
    ReportCreated::class => [
        PublishReportCreatedEvent::class,
    ],
];
```

---

### **Day 8: Tests**

#### 6.1. Feature Tests

```bash
php artisan make:test ReportTest
php artisan make:test AuthTest
```

**Example: `tests/Feature/ReportTest.php`**
```php
<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\PhanAnh;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_report(): void
    {
        $user = NguoiDung::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/reports', [
            'tieu_de' => 'Đường bị ổ gà',
            'mo_ta' => 'Đoạn đường Nguyễn Huệ có nhiều ổ gà',
            'danh_muc' => 0, // traffic
            'vi_do' => 10.8231,
            'kinh_do' => 106.6297,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tieu_de',
                    'mo_ta',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('phan_anhs', [
            'tieu_de' => 'Đường bị ổ gà',
        ]);
    }

    public function test_user_can_list_reports(): void
    {
        $user = NguoiDung::factory()->create();
        PhanAnh::factory(10)->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/reports');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'tieu_de', 'mo_ta'],
                ],
            ]);
    }
}
```

**Run tests:**
```bash
php artisan test
```

---

## ✅ Checklist cho mỗi service

### **Before coding:**
- [ ] Đọc `Database.md` của service
- [ ] Xác định dependencies (service nào cần có trước)
- [ ] Setup database trong docker-compose
- [ ] Tạo project structure

### **During coding:**
- [ ] Database migrations
- [ ] Models/Entities
- [ ] API endpoints (CRUD)
- [ ] Request validation
- [ ] Response resources/DTOs
- [ ] Event publishing (nếu cần)
- [ ] Event consuming (nếu cần)
- [ ] Unit tests
- [ ] Integration tests

### **After coding:**
- [ ] API documentation (OpenAPI/Swagger)
- [ ] README.md với setup instructions
- [ ] Docker/Dockerfile
- [ ] Environment variables documented
- [ ] Code review
- [ ] Merge to develop

---

## 🎯 Best Practices

### **1. Code Organization**
```
service-name/
├── src/
│   ├── controllers/
│   ├── models/
│   ├── services/
│   ├── repositories/
│   ├── events/
│   └── utils/
├── tests/
├── config/
├── .env.example
├── README.md
└── package.json / composer.json
```

### **2. API Response Format**
```json
{
  "success": true,
  "data": {...},
  "message": "Success",
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 100
  }
}
```

### **3. Error Response Format**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": [...]
  }
}
```

### **4. Event Message Format**
```json
{
  "event_id": "uuid",
  "event_type": "ReportCreated",
  "version": "1.0",
  "timestamp": "2025-01-15T10:30:00Z",
  "source": "core-api",
  "data": {...}
}
```

### **5. Git Workflow**
```bash
# Create feature branch
git checkout -b feature/core-api-reports

# Commit frequently
git add .
git commit -m "feat(core-api): implement reports CRUD"

# Push and create PR
git push origin feature/core-api-reports
```

### **6. Environment Variables**
- Không commit `.env` files
- Tạo `.env.example` với sample values
- Document tất cả environment variables trong README

### **7. Testing**
- Aim for 80%+ code coverage
- Test happy paths và edge cases
- Mock external dependencies
- CI/CD tự động chạy tests

---

## 📚 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [RabbitMQ Documentation](https://www.rabbitmq.com/docs)
- [Docker Documentation](https://docs.docker.com/)
- [API Best Practices](https://restfulapi.net/)

---

## 🚀 Quick Start Commands

```bash
# Start infrastructure
cd infrastructure/docker
docker-compose -f docker-compose.dev.yml up -d

# Setup Core API
cd ../../CoreAPI
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve

# Run tests
php artisan test

# Start development
composer run dev
```

---

**Last Updated:** November 11, 2025  
**Version:** 1.0.0  
**Status:** Ready to code 🚀
