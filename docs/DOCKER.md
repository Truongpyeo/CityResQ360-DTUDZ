# 🐳 Hướng Dẫn Docker - CityResQ360

Hướng dẫn cài đặt và chạy CityResQ360 trên Docker.

## 📋 Yêu Cầu

- **Docker Desktop** đã được cài đặt và đang chạy
- **Docker Compose** (thường đi kèm với Docker Desktop)
- Tối thiểu **4GB RAM** trống
- Tối thiểu **10GB** dung lượng ổ cứng trống

## 🚀 Cài Đặt và Chạy

### Bước 1: Clone Repository

```bash
git clone <repository-url>
cd CityResQ360-DTUDZ
```

### Bước 2: Khởi Động Tất Cả Services

```bash
docker-compose up -d --build
```

Lệnh này sẽ:
- Build các Docker images cần thiết
- Tạo và khởi động tất cả containers
- Chạy migrations và seeders tự động cho CoreAPI
- Khởi động tất cả services ở chế độ background

### Bước 3: Kiểm Tra Trạng Thái

```bash
docker-compose ps
```

Tất cả services phải có status là `Up` hoặc `Up (healthy)`.

### Bước 4: Xem Logs (Tùy Chọn)

```bash
# Xem logs của tất cả services
docker-compose logs -f

# Xem logs của một service cụ thể
docker-compose logs -f coreapi
docker-compose logs -f media-service
```

## 🛑 Dừng Services

```bash
# Dừng tất cả services (giữ lại containers)
docker-compose stop

# Dừng và xóa containers
docker-compose down

# Dừng và xóa containers + volumes (xóa dữ liệu)
docker-compose down -v
```

## 🔄 Khởi Động Lại Services

```bash
# Khởi động lại tất cả services
docker-compose restart

# Khởi động lại một service cụ thể
docker-compose restart coreapi
```

## 📦 Services và Ports

| Service | Port | URL | Mô Tả |
|---------|------|-----|-------|
| **CoreAPI** | 8000 | http://localhost:8000 | Laravel API Server |
| **CoreAPI Vite** | 5173 | http://localhost:5173 | Vite Dev Server (Frontend) |
| **MediaService** | 8004 | http://localhost:8004 | Media Upload Service |
| **MySQL** | 3306 | localhost:3306 | Database chính |
| **MongoDB** | 27017 | localhost:27017 | Database cho MediaService |
| **Redis** | 6379 | localhost:6379 | Cache & Queue |
| **RabbitMQ** | 5672 | localhost:5672 | Message Broker |
| **RabbitMQ Management** | 15672 | http://localhost:15672 | RabbitMQ Web UI |
| **MinIO** | 9000 | localhost:9000 | Object Storage API |
| **MinIO Console** | 9001 | http://localhost:9001 | MinIO Web UI |

## 🔐 Thông Tin Đăng Nhập

### Admin Panel (CoreAPI)

Sau khi chạy `docker-compose up`, database đã được migrate và seed tự động. Bạn có thể đăng nhập với các tài khoản sau:

#### Super Admin (Master)
- **Email:** `admin@master.com`
- **Password:** `123456`
- **Quyền:** Toàn quyền hệ thống (is_master = true)

#### Data Admin
- **Email:** `dataadmin@cityresq360.com`
- **Password:** `password123`
- **Quyền:** Quản lý dữ liệu

#### Agency Admin
- **Email:** `agencyadmin@cityresq360.com`
- **Password:** `password123`
- **Quyền:** Quản lý cơ quan

**URL Admin Panel:** http://localhost:8000/admin

### RabbitMQ Management

- **URL:** http://localhost:15672
- **Username:** `cityresq`
- **Password:** `cityresq_password`

### MinIO Console

- **URL:** http://localhost:9001
- **Username:** `minioadmin`
- **Password:** `minioadmin`

### MySQL Database

- **Host:** `localhost` (từ host) hoặc `mysql` (từ container)
- **Port:** `3306`
- **Database:** `cityresq_db`
- **Username:** `cityresq`
- **Password:** `cityresq_password`
- **Root Password:** `root_password`

### MongoDB

- **Host:** `localhost` (từ host) hoặc `mongodb` (từ container)
- **Port:** `27017`
- **Username:** `cityresq`
- **Password:** `cityresq_password`
- **Authentication Database:** `admin`

## 🔧 Các Lệnh Hữu Ích

### Xem Logs

```bash
# Logs của tất cả services
docker-compose logs -f

# Logs của CoreAPI
docker-compose logs -f coreapi

# Logs của MediaService
docker-compose logs -f media-service

# Logs 50 dòng cuối
docker-compose logs --tail 50 coreapi
```

### Truy Cập Container

```bash
# Truy cập vào CoreAPI container
docker exec -it cityresq-coreapi sh

# Truy cập vào MySQL
docker exec -it cityresq-mysql mysql -u cityresq -p cityresq_db

# Truy cập vào MongoDB
docker exec -it cityresq-mongodb mongosh -u cityresq -p cityresq_password --authenticationDatabase admin
```

### Chạy Artisan Commands

```bash
# Chạy migration
docker exec cityresq-coreapi php artisan migrate

# Chạy seeder
docker exec cityresq-coreapi php artisan db:seed

# Chạy migration + seeder
docker exec cityresq-coreapi php artisan migrate --seed

# Xóa cache
docker exec cityresq-coreapi php artisan cache:clear
docker exec cityresq-coreapi php artisan config:clear
docker exec cityresq-coreapi php artisan route:clear
```

### Rebuild Services

```bash
# Rebuild một service cụ thể
docker-compose build coreapi
docker-compose up -d coreapi

# Rebuild tất cả services
docker-compose build
docker-compose up -d
```

## 🧪 Kiểm Tra Services

### Test CoreAPI

```bash
# Test health endpoint
curl http://localhost:8000

# Test admin panel
curl http://localhost:8000/admin
```

### Test MediaService

```bash
# Test health endpoint
curl http://localhost:8004/health

# Kết quả mong đợi: {"status":"ok","service":"MediaService"}
```

### Test Database Connections

```bash
# Test MySQL
docker exec cityresq-mysql mysqladmin ping -h localhost -u cityresq -pcityresq_password

# Test MongoDB
docker exec cityresq-mongodb mongosh -u cityresq -p cityresq_password --authenticationDatabase admin --eval "db.adminCommand('ping')"
```

## 🐛 Troubleshooting

### Services không khởi động

```bash
# Kiểm tra logs
docker-compose logs [service-name]

# Kiểm tra trạng thái
docker-compose ps

# Khởi động lại
docker-compose restart [service-name]
```

### Port đã được sử dụng

Nếu gặp lỗi `port is already in use`:

```bash
# Tìm process đang dùng port
lsof -i :8000
lsof -i :3306

# Dừng process hoặc đổi port trong docker-compose.yml
```

### Database connection errors

```bash
# Đảm bảo MySQL/MongoDB đã healthy
docker-compose ps

# Kiểm tra network
docker network ls
docker network inspect cityresq360-dtudz_cityresq-network
```

### Xóa và tạo lại tất cả

```bash
# Dừng và xóa tất cả (bao gồm volumes)
docker-compose down -v

# Xóa images
docker-compose down --rmi all

# Build và khởi động lại
docker-compose up -d --build
```

## 📝 Lưu Ý

1. **Lần đầu chạy:** CoreAPI sẽ tự động:
   - Chạy `composer install`
   - Chạy `npm install`
   - Chạy `php artisan migrate --force`
   - Chạy `php artisan db:seed --force`
   - Cache config, routes, views
   - Khởi động Laravel server và Vite dev server

2. **Dữ liệu:** Dữ liệu được lưu trong Docker volumes, sẽ không mất khi restart containers.

3. **Performance:** Lần đầu build có thể mất 5-10 phút tùy vào tốc độ mạng và máy tính.

4. **ARM64:** Một số services có thể không hỗ trợ ARM64. Nếu gặp lỗi, hãy kiểm tra logs.

## 🔗 Liên Kết Hữu Ích

- **CoreAPI:** http://localhost:8000
- **Admin Panel:** http://localhost:8000/admin
- **MediaService Health:** http://localhost:8004/health
- **RabbitMQ Management:** http://localhost:15672
- **MinIO Console:** http://localhost:9001

## 📞 Hỗ Trợ

Nếu gặp vấn đề, hãy:
1. Kiểm tra logs: `docker-compose logs`
2. Kiểm tra trạng thái: `docker-compose ps`
3. Xem phần Troubleshooting ở trên

