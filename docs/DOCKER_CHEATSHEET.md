# Docker Commands Cheat Sheet - CityResQ360
# Tài liệu tổng hợp các lệnh Docker thường dùng cho dự án CityResQ360

## 🚀 Quản lý Container (Container Management)

### Khởi động/Dừng Services (Start/Stop Services)
```bash
# Khởi động tất cả services
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d

# Khởi động service cụ thể
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d coreapi
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d media-service

# Dừng tất cả services
docker-compose -f infrastructure/docker/docker-compose.production.yml down

# Dừng và xóa volumes (⚠️ MẤT DỮ LIỆU!)
docker-compose -f infrastructure/docker/docker-compose.production.yml down -v

# Khởi động lại service
docker-compose -f infrastructure/docker/docker-compose.production.yml restart coreapi
docker-compose -f infrastructure/docker/docker-compose.production.yml restart media-service
```

### Build lại Services (Rebuild Services)
```bash
# Build lại tất cả từ đầu (không dùng cache)
docker-compose -f infrastructure/docker/docker-compose.production.yml build --no-cache

# Build lại service cụ thể
docker-compose -f infrastructure/docker/docker-compose.production.yml build --no-cache coreapi

# Build lại và khởi động lại
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d --build --force-recreate coreapi

# Build lại với volumes mới (cài đặt sạch)
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d --build --force-recreate -V media-service
```

## 📊 Giám sát & Logs (Monitoring & Logs)

### Xem Logs (View Logs)
```bash
# Theo dõi logs (tất cả services)
docker-compose -f infrastructure/docker/docker-compose.production.yml logs -f

# Xem logs của service cụ thể
docker logs --tail 50 cityresq-coreapi
docker logs --tail 50 cityresq-media-service

# Theo dõi logs real-time
docker logs -f cityresq-coreapi
docker logs -f cityresq-media-service

# Kiểm tra trạng thái container
docker ps | grep cityresq
docker ps -a | grep cityresq  # Bao gồm cả containers đã dừng
```

### Logs của Laravel (Laravel Logs)
```bash
# Xem file log Laravel
docker exec -it cityresq-coreapi cat /var/www/html/storage/logs/laravel.log

# Theo dõi log Laravel real-time
docker exec -it cityresq-coreapi tail -f /var/www/html/storage/logs/laravel.log

# Xóa sạch file log Laravel
docker exec -it cityresq-coreapi truncate -s 0 /var/www/html/storage/logs/laravel.log
```

## 🔧 Lệnh Laravel Artisan (Laravel Artisan Commands)

### Quản lý Cache (Cache Management)
```bash
# Xóa tất cả cache
docker exec -it cityresq-coreapi php artisan cache:clear
docker exec -it cityresq-coreapi php artisan config:clear
docker exec -it cityresq-coreapi php artisan route:clear
docker exec -it cityresq-coreapi php artisan view:clear

# Cache lại cấu hình
docker exec -it cityresq-coreapi php artisan config:cache
docker exec -it cityresq-coreapi php artisan route:cache
```

### Cơ sở dữ liệu (Database)
```bash
# Chạy migrations
docker exec -it cityresq-coreapi php artisan migrate --force

# Chạy seeders (tạo dữ liệu mẫu)
docker exec -it cityresq-coreapi php artisan db:seed --force
docker exec -it cityresq-coreapi php artisan db:seed --class=ModuleDefinitionsSeeder

# Rollback migrations (hoàn tác migration)
docker exec -it cityresq-coreapi php artisan migrate:rollback --force

# Migration mới hoàn toàn (⚠️ MẤT DỮ LIỆU!)
docker exec -it cityresq-coreapi php artisan migrate:fresh --force --seed
```

### Cài đặt ứng dụng (Application Setup)
```bash
# Tạo APP_KEY (khóa mã hóa)
docker exec -it cityresq-coreapi php artisan key:generate --force

# Tối ưu hóa ứng dụng
docker exec -it cityresq-coreapi php artisan optimize
docker exec -it cityresq-coreapi php artisan optimize:clear
```

## 🗄️ Quản lý Database (Database Management)

### MySQL
```bash
# Truy cập MySQL CLI
docker exec -it cityresq-mysql mysql -u root -p

# Sao lưu database
docker exec cityresq-mysql mysqldump -u root -p${MYSQL_PASSWORD} --all-databases > backup_$(date +%Y%m%d).sql

# Khôi phục database từ file backup
cat backup.sql | docker exec -i cityresq-mysql mysql -u root -p${MYSQL_PASSWORD}

# Kiểm tra kết nối database
docker exec -it cityresq-coreapi php artisan db:show
```

### MongoDB
```bash
# Truy cập MongoDB CLI
docker exec -it cityresq-mongodb mongosh -u cityresq -p ${MONGODB_PASSWORD} --authenticationDatabase admin

# Sao lưu MongoDB
docker exec cityresq-mongodb mongodump --username=cityresq --password=${MONGODB_PASSWORD} --authenticationDatabase=admin --out=/backup

# Liệt kê các database
docker exec -it cityresq-mongodb mongosh -u cityresq -p ${MONGODB_PASSWORD} --authenticationDatabase admin --eval "show dbs"
```

### Redis
```bash
# Truy cập Redis CLI
docker exec -it cityresq-redis redis-cli

# Xóa toàn bộ cache
docker exec -it cityresq-redis redis-cli FLUSHALL

# Lấy tất cả keys
docker exec -it cityresq-redis redis-cli KEYS '*'
```

## 🧹 Lệnh dọn dẹp (Cleanup Commands)

### Xóa Images cũ (Remove Old Images)
```bash
# Liệt kê tất cả images
docker images

# Xóa image cụ thể
docker rmi <image_id>

# Xóa các images của CityResQ
docker images | grep -E "cityresq|docker-.*-service" | awk '{print $3}' | xargs docker rmi -f

# Xóa các dangling images (images không tên)
docker image prune -f

# Xóa tất cả images không dùng
docker image prune -a -f
```

### Xóa Containers (Remove Containers)
```bash
# Xóa các containers đã dừng
docker container prune -f

# Xóa tất cả containers của CityResQ
docker ps -a | grep cityresq | awk '{print $1}' | xargs docker rm -f
```

### Xóa Volumes (Remove Volumes)
```bash
# Liệt kê volumes
docker volume ls

# Xóa volume cụ thể
docker volume rm <volume_name>

# Xóa tất cả volumes không dùng (⚠️ MẤT DỮ LIỆU!)
docker volume prune -f
```

### Dọn dẹp toàn bộ (Complete Cleanup)
```bash
# Xóa tất cả mọi thứ (⚠️ TÙYCHỌN HẠT NHÂN!)
docker system prune -a --volumes -f
```

## 🔍 Gỡ lỗi (Debugging)

### Thực thi lệnh trong Container (Execute Commands in Container)
```bash
# Truy cập shell của container
docker exec -it cityresq-coreapi bash
docker exec -it cityresq-media-service sh

# Kiểm tra biến môi trường
docker exec -it cityresq-coreapi env | grep -E "DB_|JWT_|APP_"
docker exec -it cityresq-media-service env | grep JWT_SECRET

# Kiểm tra kết nối mạng
docker exec -it cityresq-coreapi ping -c 3 mysql
docker exec -it cityresq-coreapi ping -c 3 media-service

# Xem chi tiết container
docker inspect cityresq-coreapi
docker inspect cityresq-media-service

# Kiểm tra network của container
docker network inspect cityresq-network
```

### Thao tác với Files (File Operations)
```bash
# Copy file từ container ra ngoài
docker cp cityresq-coreapi:/var/www/html/.env ./coreapi.env

# Copy file vào container
docker cp local-file.txt cityresq-coreapi:/var/www/html/

# Xem nội dung file
docker exec -it cityresq-coreapi cat /var/www/html/.env
```

## 📦 Lệnh theo Service (Service-Specific Commands)

### MinIO (Lưu trữ object S3)
```bash
# Truy cập MinIO Console
# http://<server-ip>:9001

# Tạo bucket qua CLI
docker exec -it cityresq-minio mc alias set myminio http://localhost:9000 admin ${MINIO_ROOT_PASSWORD}
docker exec -it cityresq-minio mc mb myminio/cityresq-media
```

### RabbitMQ (Message Queue)
```bash
# Truy cập RabbitMQ Management UI
# http://<server-ip>:15672

# Liệt kê các queues
docker exec -it cityresq-rabbitmq rabbitmqctl list_queues
```

## 🚨 Lệnh khẩn cấp (Emergency Commands)

### Service không khởi động (Service Not Starting)
```bash
# Kiểm tra trạng thái health
docker inspect --format='{{json .State.Health}}' cityresq-coreapi

# Kiểm tra tại sao container bị crash
docker logs --tail 100 cityresq-coreapi
docker inspect cityresq-coreapi | grep -A 10 State

# Buộc tạo lại service
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d --force-recreate coreapi
```

### Reset toàn bộ (⚠️ TẤT CẢ DỮ LIỆU SẼ MẤT!)
```bash
cd /opt/CityResQ360

# Dừng tất cả
docker-compose -f infrastructure/docker/docker-compose.production.yml down -v

# Xóa images
docker images | grep -E "cityresq|docker-.*-service" | awk '{print $3}' | xargs -r docker rmi -f

# Xóa file .env
rm -f infrastructure/docker/.env
rm -f modules/CoreAPI/.env

# Pull code mới nhất
git pull origin develop

# Triển khai lại từ đầu
sudo bash scripts/deploy/deploy.sh
```

## 📝 Tham khảo nhanh (Quick Reference)

### Đường dẫn rút gọn (Path Shortcuts)
```bash
# File docker compose
COMPOSE_FILE="infrastructure/docker/docker-compose.production.yml"

# Alias cho docker-compose (thêm vào ~/.bashrc hoặc ~/.zshrc)
alias dc='docker-compose -f infrastructure/docker/docker-compose.production.yml'

# Cách dùng
dc up -d
dc logs -f coreapi
dc restart media-service
```

### Quy trình thường dùng (Common Workflows)

#### Triển khai code mới (Deploy New Code)
```bash
git pull origin develop
docker-compose -f infrastructure/docker/docker-compose.production.yml build --no-cache
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d --force-recreate
docker exec -it cityresq-coreapi php artisan migrate --force
docker exec -it cityresq-coreapi php artisan config:cache
```

#### Sửa lỗi 500 Error (Fix 500 Error)
```bash
# Kiểm tra logs
docker logs --tail 100 cityresq-coreapi
docker exec -it cityresq-coreapi tail -f /var/www/html/storage/logs/laravel.log

# Xóa cache
docker exec -it cityresq-coreapi php artisan cache:clear
docker exec -it cityresq-coreapi php artisan config:clear

# Khởi động lại
docker-compose -f infrastructure/docker/docker-compose.production.yml restart coreapi
```

#### Sửa lỗi MediaService (MediaService Issues)
```bash
# Kiểm tra trạng thái
docker ps | grep media
docker logs --tail 50 cityresq-media-service

# Build lại
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d --build --force-recreate media-service

# Xác minh
docker exec -it cityresq-media-service env | grep JWT_SECRET
```
