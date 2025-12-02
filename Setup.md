# 🗂️ Hướng dẫn cài đặt CityResQ360

> Hướng dẫn chi tiết cài đặt và chạy hệ thống CityResQ360 từ GitHub về máy local

## 🖥️ Yêu cầu hệ thống

- **CPU**: 4 cores
- **RAM**: 8 GB
- **Ổ cứng**: 50 GB trống
- **Mạng**: Kết nối internet ổn định

## ⚠️ Nếu không sử dụng Docker

- Nếu bạn không dùng docker thì xem file [docs/BUILD_WITHOUT_DOCKER.md](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/blob/master/docs/BUILD_WITHOUT_DOCKER.md)

### Phần mềm cần cài đặt

| Phần mềm           | Phiên bản | Mục đích          |
| ------------------ | --------- | ----------------- |
| **Docker**         | 20.10+    | Container runtime |
| **Docker Compose** | 2.0+      | Orchestration     |
| **Git**            | 2.30+     | Version control   |

> **Lưu ý**: Hệ thống sử dụng Docker để chạy tất cả services, bạn **KHÔNG CẦN** cài đặt PHP, Node.js, Python, MySQL, PostgreSQL, v.v. trên máy local.

---

## 🐳 Cài đặt Docker

### Windows

1. **Tải Docker Desktop**

   - Truy cập: https://www.docker.com/products/docker-desktop
   - Tải bản Windows và cài đặt

2. **Cài đặt WSL 2** (nếu chưa có)

   ```powershell
   wsl --install
   ```

3. **Khởi động Docker Desktop**

   - Mở Docker Desktop từ Start Menu
   - Đợi Docker khởi động hoàn tất (icon Docker màu xanh)

4. **Kiểm tra cài đặt**
   ```powershell
   docker --version
   docker compose version
   ```

### macOS

1. **Tải Docker Desktop**

   - Truy cập: https://www.docker.com/products/docker-desktop
   - Tải bản macOS và cài đặt

2. **Hoặc dùng Homebrew**

   ```bash
   brew install --cask docker
   ```

3. **Khởi động Docker Desktop**

   - Mở Docker Desktop từ Applications
   - Đợi Docker khởi động hoàn tất

4. **Kiểm tra cài đặt**
   ```bash
   docker --version
   docker compose version
   ```

### Linux (Ubuntu/Debian)

```bash
# Cập nhật package index
sudo apt update

# Cài đặt dependencies
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common

# Thêm Docker GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Thêm Docker repository
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Cài đặt Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Khởi động Docker
sudo systemctl start docker
sudo systemctl enable docker

# Thêm user vào docker group (để chạy docker không cần sudo)
sudo usermod -aG docker $USER

# Logout và login lại để áp dụng thay đổi, sau đó kiểm tra
docker --version
docker compose version
```

---

## 📥 Cài đặt hệ thống

### Bước 1: Clone repository từ GitHub

```bash
# Clone project về máy
git clone https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ.git

# Di chuyển vào thư mục project
cd CityResQ360-DTUDZ
```

### Bước 2: Cấu hình Environment Variables

#### Tạo file .env cho CoreAPI

```bash
# Copy file .env.example
cp modules/CoreAPI/.env.example modules/CoreAPI/.env
```

Mở file `modules/CoreAPI/.env` và cập nhật các thông tin sau (nếu cần):

```env
APP_NAME=CityResQ360
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cityresq_db
DB_USERNAME=cityresq
DB_PASSWORD=cityresq_password

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# RabbitMQ
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=cityresq
RABBITMQ_PASSWORD=cityresq_password
```

#### Tạo file .env chung (Optional)

Tạo file `.env` ở thư mục gốc project:

```bash
# Windows (PowerShell)
New-Item -Path .env -ItemType File

# Linux/macOS
touch .env
```

Thêm nội dung vào file `.env`:

```env
# Database Passwords
MYSQL_ROOT_PASSWORD=root_password
MYSQL_PASSWORD=cityresq_password
POSTGRES_PASSWORD=cityresq_password
MONGODB_PASSWORD=cityresq_password

# RabbitMQ
RABBITMQ_PASSWORD=cityresq_password

# MinIO
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin

# ClickHouse
CLICKHOUSE_PASSWORD=cityresq_password

# JWT Secret
JWT_SECRET=your-secret-key-change-this-in-production
```

### Bước 3: Cấu hình MQTT (Mosquitto)

Tạo file cấu hình cho MQTT broker:

```bash
# Tạo thư mục config nếu chưa có
mkdir -p infrastructure/mosquitto/config
mkdir -p infrastructure/mosquitto/data
mkdir -p infrastructure/mosquitto/log
```

Tạo file `infrastructure/mosquitto/config/mosquitto.conf`:

```conf
listener 1883
allow_anonymous true
persistence true
persistence_location /mosquitto/data/
log_dest file /mosquitto/log/mosquitto.log
```

---

## 🚀 Khởi động services

### Phương pháp 1: Khởi động toàn bộ hệ thống (Đơn giản nhất)

```bash
# Di chuyển vào thư mục docker
cd infrastructure/docker

# Khởi động tất cả services
docker compose up -d

# Xem logs để theo dõi quá trình khởi động
docker compose logs -f
```

> **Lưu ý**: Lần đầu tiên chạy sẽ mất 10-15 phút để tải images và build containers.

### Phương pháp 2: Khởi động từng nhóm services (Khuyến nghị)

Cách này giúp bạn kiểm soát tốt hơn quá trình khởi động:

**Bước 1: Khởi động Databases & Infrastructure**

```bash
cd infrastructure/docker

docker compose up -d mysql postgres timescaledb postgres-incident postgres-aiml postgres-floodeye mongodb redis rabbitmq minio mqtt opensearch clickhouse
```

Đợi khoảng **30-60 giây** để các database khởi động hoàn tất.

**Bước 2: Kiểm tra databases đã sẵn sàng**

```bash
docker compose ps
```

Tất cả containers phải có trạng thái `Up (healthy)` hoặc `Up`.

**Bước 3: Khởi động Application Services**

```bash
docker compose up -d coreapi media-service notification-service wallet-service incident-service iot-service
```

**Bước 4: Khởi động AI/ML Services (Optional)**

```bash
docker compose up -d aiml-service search-service floodeye-service analytics-service
```

**Bước 5: Khởi động Web/Mobile App (Optional)**

```bash
docker compose up -d app-mobile
```

### Phương pháp 3: Sử dụng Script quản lý

Dự án có sẵn script để quản lý services dễ dàng hơn:

```bash
# Linux/macOS
bash scripts/local/run.sh

# Windows (Git Bash)
bash scripts/local/run.sh
```

Script sẽ hiển thị menu:

```
========================================
CityResQ360 - Local Docker Manager
========================================

Choose an option:
  1) Start all services
  2) Stop all services
  3) Restart all services
  4) Clean rebuild (remove everything)
  5) View logs
  6) Check status
  7) Run migrations
  8) Test endpoints
  9) Open shells
  0) Exit
========================================
```

Chọn option **1** để khởi động tất cả services.

---

## 🔧 Cài đặt sau khi khởi động

### 1. Chạy migrations cho CoreAPI

Sau khi các containers đã chạy, bạn cần chạy migrations để tạo database schema:

```bash
# Vào container CoreAPI
docker exec -it cityresq-coreapi bash

# Chạy migrations
php artisan migrate --seed

# Generate application key
php artisan key:generate

# Cache config
php artisan config:cache

# Thoát container
exit
```

### 2. Tạo bucket cho MinIO

MinIO là nơi lưu trữ media files (ảnh, video).

**Cách 1: Qua Web UI**

1. Truy cập MinIO Console: http://localhost:9001
2. Đăng nhập:
   - Username: `minioadmin`
   - Password: `minioadmin`
3. Tạo bucket mới tên `cityresq-media`

**Cách 2: Qua Command Line**

```bash
# Tạo alias cho MinIO
docker run --rm -it --network infrastructure_cityresq-network minio/mc alias set myminio http://minio:9000 minioadmin minioadmin

# Tạo bucket
docker run --rm -it --network infrastructure_cityresq-network minio/mc mb myminio/cityresq-media

# Set public access (optional)
docker run --rm -it --network infrastructure_cityresq-network minio/mc anonymous set download myminio/cityresq-media
```

### 3. Khởi tạo extensions cho PostgreSQL

**WalletService (PostgreSQL)**

```bash
docker exec -it cityresq-postgres psql -U cityresq -d wallet_db -c "CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";"
```

**FloodEyeService (PostGIS)**

```bash
docker exec -it cityresq-postgres-floodeye psql -U cityresq -d floodeye_db -c "CREATE EXTENSION IF NOT EXISTS postgis;"
```

**IoTService (TimescaleDB)**

```bash
docker exec -it cityresq-timescaledb psql -U cityresq -d iot_db -c "CREATE EXTENSION IF NOT EXISTS timescaledb;"
```

---

## ✅ Kiểm tra hệ thống

### 1. Kiểm tra containers đang chạy

```bash
cd infrastructure/docker
docker compose ps
```

Kết quả mong đợi: Tất cả services có trạng thái `Up` hoặc `Up (healthy)`.

### 2. Kiểm tra logs

```bash
# Xem logs tất cả services
docker compose logs

# Xem logs của service cụ thể
docker compose logs coreapi
docker compose logs media-service

# Follow logs realtime
docker compose logs -f notification-service
```

### 3. Truy cập các services

| Service                   | URL                                     | Credentials                  |
| ------------------------- | --------------------------------------- | ---------------------------- |
| **CoreAPI**               | http://localhost:8000                   | -                            |
| **API Documentation**     | http://localhost:8000/api/documentation | -                            |
| **Web App**               | http://localhost:3000                   | -                            |
| **MinIO Console**         | http://localhost:9001                   | minioadmin / minioadmin      |
| **RabbitMQ Management**   | http://localhost:15672                  | cityresq / cityresq_password |
| **OpenSearch Dashboards** | http://localhost:5601                   | -                            |

### 4. Test API endpoints

```bash
# Health check CoreAPI
curl http://localhost:8000/api/health

# Health check MediaService
curl http://localhost:8004/health

# Health check NotificationService
curl http://localhost:8006/health

# Health check WalletService
curl http://localhost:8005/health
```

Nếu các API trả về response (không lỗi connection), nghĩa là hệ thống đã chạy thành công!

### 5. Kiểm tra kết nối database

**MySQL (CoreAPI)**

```bash
docker exec -it cityresq-mysql mysql -u cityresq -pcityresq_password -e "SHOW DATABASES;"
```

**PostgreSQL (WalletService)**

```bash
docker exec -it cityresq-postgres psql -U cityresq -d wallet_db -c "\dt"
```

**MongoDB (MediaService)**

```bash
docker exec -it cityresq-mongodb mongosh -u cityresq -p cityresq_password --authenticationDatabase admin --eval "show dbs"
```

**Redis**

```bash
docker exec -it cityresq-redis redis-cli ping
```

Kết quả mong đợi: `PONG`

---

## 🛑 Dừng và xóa hệ thống

### Dừng tất cả services

```bash
cd infrastructure/docker
docker compose stop
```

### Dừng và xóa containers (giữ lại data)

```bash
docker compose down
```

### Xóa hoàn toàn (bao gồm volumes/data)

```bash
# ⚠️ CẢNH BÁO: Lệnh này sẽ XÓA TẤT CẢ DỮ LIỆU
docker compose down -v
```

### Clean rebuild toàn bộ hệ thống

```bash
# Sử dụng script rebuild
bash scripts/local/rebuild-docker.sh
```

---

## 🆘 Troubleshooting

### Lỗi: Port already in use

**Nguyên nhân**: Port đã được sử dụng bởi ứng dụng khác (ví dụ: XAMPP, WAMP, MySQL local)

**Giải pháp**:

1. **Kiểm tra port nào đang bị chiếm**:

   ```bash
   # Windows
   netstat -ano | findstr :3306
   netstat -ano | findstr :8000

   # Linux/macOS
   lsof -i :3306
   lsof -i :8000
   ```

2. **Dừng ứng dụng đang chiếm port**:

   - Nếu là XAMPP: Tắt MySQL và Apache trong XAMPP Control Panel
   - Hoặc thay đổi port trong `docker-compose.yml`

3. **Thay đổi port trong docker-compose.yml** (nếu cần):
   ```yaml
   mysql:
     ports:
       - "3307:3306" # Đổi từ 3306 sang 3307
   ```

### Lỗi: Container unhealthy hoặc không khởi động

**Nguyên nhân**: Service không khởi động đúng cách

**Giải pháp**:

```bash
# Xem logs của container
docker compose logs [service-name]

# Ví dụ
docker compose logs mysql
docker compose logs coreapi

# Restart container
docker compose restart [service-name]

# Rebuild container
docker compose up -d --build [service-name]
```

### Lỗi: Permission denied (Linux)

**Nguyên nhân**: User chưa có quyền chạy Docker

**Giải pháp**:

```bash
# Thêm user vào docker group
sudo usermod -aG docker $USER

# Logout và login lại
# Hoặc chạy lệnh này để áp dụng ngay
newgrp docker
```

### Lỗi: Out of memory

**Nguyên nhân**: Docker không đủ RAM

**Giải pháp**:

1. **Tăng memory limit cho Docker Desktop**:

   - Mở Docker Desktop
   - Settings → Resources → Memory
   - Tăng lên ít nhất 4-6 GB

2. **Hoặc giảm số services chạy đồng thời**:
   ```bash
   # Chỉ chạy services cần thiết
   docker compose up -d mysql redis rabbitmq coreapi
   ```

### Lỗi: Database connection refused

**Nguyên nhân**: Database chưa khởi động xong

**Giải pháp**:

```bash
# Đợi database khởi động (30-60 giây)
docker compose logs mysql
docker compose logs postgres

# Kiểm tra health status
docker compose ps

# Nếu vẫn lỗi, restart database
docker compose restart mysql
```

### Lỗi: Cannot connect to Docker daemon

**Nguyên nhân**: Docker Desktop chưa khởi động

**Giải pháp**:

1. Khởi động Docker Desktop
2. Đợi Docker khởi động hoàn tất (icon Docker trên taskbar/menu bar phải màu xanh)
3. Thử lại lệnh

### Lỗi: Build failed hoặc image pull failed

**Nguyên nhân**: Kết nối internet không ổn định hoặc Docker Hub bị chặn

**Giải pháp**:

```bash
# Thử lại build
docker compose build --no-cache

# Hoặc pull image trước
docker compose pull

# Nếu Docker Hub bị chặn, cấu hình Docker mirror (VN)
# Thêm vào Docker Desktop Settings → Docker Engine:
{
  "registry-mirrors": ["https://mirror.gcr.io"]
}
```

### Xóa và rebuild hoàn toàn

Nếu gặp lỗi không giải quyết được, thử clean rebuild:

```bash
# Dừng tất cả containers
cd infrastructure/docker
docker compose down -v

# Xóa tất cả images của CityResQ360
docker images | grep cityresq | awk '{print $3}' | xargs docker rmi -f

# Xóa tất cả volumes
docker volume ls | grep cityresq | awk '{print $2}' | xargs docker volume rm

# Clean Docker system
docker system prune -a --volumes

# Rebuild lại từ đầu
docker compose up -d --build
```

---

## 📚 Các lệnh Docker hữu ích

```bash
# Xem tất cả containers đang chạy
docker ps

# Xem tất cả containers (kể cả đã dừng)
docker ps -a

# Xem logs của container
docker logs [container-name]
docker logs -f [container-name]  # Follow logs

# Vào shell của container
docker exec -it [container-name] bash
docker exec -it [container-name] sh

# Restart container
docker restart [container-name]

# Stop container
docker stop [container-name]

# Remove container
docker rm [container-name]

# Xem resource usage
docker stats

# Xem images
docker images

# Xem volumes
docker volume ls

# Xem networks
docker network ls
```

---

## 📞 Hỗ trợ

Nếu gặp vấn đề không giải quyết được, vui lòng:

1. **Tạo issue** tại: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/issues
2. **Liên hệ team**:
   - Lê Thanh Trường: thanhtruong23111999@gmail.com
   - Nguyễn Văn Nhân: vannhan130504@gmail.com
   - Nguyễn Ngọc Duy Thái: kkdn011@gmail.com

---

## 📚 Tài liệu bổ sung

- [README.md](README.md) - Tổng quan dự án
- [CONTRIBUTING.md](CONTRIBUITING.md) - Hướng dẫn đóng góp
- [Documentation](https://nguyenthai11103.github.io/DTU-CityResQ360-documents/) - Tài liệu chi tiết

---

## 🎯 Tóm tắt các bước cài đặt

1. ✅ Cài đặt Docker Desktop
2. ✅ Clone repository từ GitHub
3. ✅ Cấu hình file .env
4. ✅ Chạy `docker compose up -d`
5. ✅ Chạy migrations: `docker exec -it cityresq-coreapi php artisan migrate`
6. ✅ Tạo MinIO bucket
7. ✅ Truy cập http://localhost:8000

**Chúc bạn cài đặt thành công! 🎉**

---

© 2025 CityResQ360 – DTU-DZ Team
