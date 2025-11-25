- Tiếp tục sửa các route Next.js động (`citizen/report/[id]`) để tương thích `PageProps` dạng Promise giúp build production không lỗi.
- Sửa lỗi Mosquitto MQTT: `Invalid max_packet_size value (0)` trong `mosquitto/config/mosquitto.conf`, thay giá trị 0 bằng 268435456 bytes (256MB).
- Cập nhật `.gitignore` của AppMobile để ignore thư mục `.next/` build output.
- **Fix critical**: Sửa lỗi 502 Bad Gateway do password tự động sinh ra chứa ký tự đặc biệt (`/`, `+`, `=`) gây MySQL/MongoDB authentication failed. Thay `openssl rand -base64` bằng `tr -dc A-Za-z0-9` để chỉ tạo password alphanumeric trong `deploy.sh`.
- **Fix production deployment**: Sửa CoreAPI để dùng Nginx + PHP-FPM production thay vì `php artisan serve`:
  - Thêm `nginx` và `supervisor` vào CoreAPI Dockerfile
  - Tạo Nginx config cho Laravel (`CoreAPI/nginx/default.conf`)
  - Dùng supervisor để chạy cả Nginx (port 80) và PHP-FPM (port 9000) trong cùng container
  - Sửa entrypoint script để chạy migrations trước khi start services
  - Xóa `command` override trong `docker-compose.production.yml` vì đã có entrypoint trong Dockerfile
  - Expose ports cho tất cả services (coreapi:8000, app-mobile:3000, media:8004, notification:8006, wallet:8005, incident:8001, iot:8002, aiml:8003, search:8007, floodeye:8008, analytics:8009)
  - Cập nhật internal service URLs từ `coreapi:8000` → `coreapi:80` trong Docker network

## 2025-11-25

- **Cải thiện deploy script**: Sửa lại `deploy.sh` để tạo `.env` chuẩn Laravel với đầy đủ config:
  - Laravel core config (APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL, APP_TIMEZONE, APP_LOCALE)
  - Database config theo Laravel convention (DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
  - Redis config (REDIS_HOST, REDIS_PORT, REDIS_DB, REDIS_CACHE_DB)
  - Queue & Broadcasting với RabbitMQ (QUEUE_CONNECTION=rabbitmq, BROADCAST_CONNECTION=rabbitmq)
  - Session & Cache (SESSION_DRIVER=redis, CACHE_STORE=redis)
  - File Storage với MinIO S3-compatible (FILESYSTEM_DISK=s3, AWS_* variables)
  - JWT Authentication (JWT_SECRET, JWT_TTL, JWT_REFRESH_TTL)
  - Microservices URLs (internal Docker network URLs)
  - Public URLs cho Frontend (NEXT_PUBLIC_API_URL, NEXT_PUBLIC_MEDIA_URL)
  - MQTT, MongoDB, Mail configuration
- Deploy script tự động tạo symlink `.env` từ `$PROJECT_DIR/.env` sang `$PROJECT_DIR/CoreAPI/.env` để Laravel đọc được
- Deploy script sử dụng `rsync` thay vì `cp -r` để tránh copy `.git`, `node_modules`, `vendor`
- Thêm các bước Laravel optimization trong deployment:
  - `php artisan migrate --force` để tự động chạy migrations
  - `php artisan db:seed --force --class=AdminSeeder` để tạo admin user mặc định
  - `php artisan config:cache` để cache config
  - `php artisan route:cache` để cache routes
  - `php artisan view:cache` để cache views
- Đảm bảo tất cả lệnh deploy chạy từ đúng thư mục `PROJECT_DIR=/opt/cityresq360`

## 2025-11-24

- Thêm Dockerfile placeholder cho các service còn thiếu (`AIMLService`, `AnalyticsService`, `FloodEyeService`, `IncidentService`, `IoTService`, `SearchService`) để bảo đảm `docker-compose.production.yml` build thành công.
- Điều chỉnh script `deploy.sh`:
  - Tự động tạo `.env` với các mật khẩu được bao bằng dấu ngoặc kép để tránh lỗi ký tự đặc biệt.
  - Bỏ bước `export` thủ công vì `docker-compose --env-file` đã đủ, tránh lỗi `not a valid identifier`.
  - Tự động tạo `WalletService/go.sum` nếu thiếu để Docker build không thất bại.
- Bổ sung `NotificationService` placeholder đầy đủ (package.json, package-lock, server.js) và cập nhật Dockerfile fallback `npm install` để bảo đảm build được dù chưa có code thật.
- Thêm mã placeholder chạy được cho các service phụ trợ:
  - `WalletService` (Go HTTP health endpoint) + dọn gọn `go.mod`/`go.sum`.
  - `IncidentService`, `IoTService` (Express health endpoint + Dockerfile mới).
  - `AIMLService`, `AnalyticsService`, `FloodEyeService`, `SearchService` (FastAPI health endpoint + requirements + Dockerfile chạy Uvicorn).
- Fix lỗi build Next.js (AppMobile) - đã build test thành công:
  - Điều chỉnh tất cả dynamic routes (`users/[id]`, `report/[id]`, `incidents/[id]`, v.v.) tuân thủ typing mới `PageProps` (params dạng Promise).
  - Sửa type error ApexCharts trong `StatsBarChart.tsx` và `StatsLineChart.tsx`: thay `width/height/radius` thành `size` cho `legend.markers`.
  - Sửa type conflict framer-motion trong `shimmer-button.tsx`: dùng `HTMLMotionProps<"button">` thay vì `ButtonHTMLAttributes`.
- Cập nhật `nginx/nginx.conf` loại bỏ cảnh báo `http2` và chuẩn bị sẵn cấu hình cho Certbot (thay đổi liên quan đến các commit `Fix nginx Error SSL*`).
- Hoàn thiện cấu hình domain/email và script deploy production (các commit `Add Domain and Email`, `Docker Deloy Production`).
- Hoàn thiện service RabbitMQ + MediaService + MinIO chạy trong Docker Compose cho production.

## 2025-11-21

- Hoàn thiện nhánh `develop` với nền tảng dịch vụ lõi, đảm bảo build Docker cơ bản chạy được trên môi trường master (`Done Master`).

## 2025-11-18

- Merge chuỗi PR document + frontend:
  - `feat/document`, `feat/app` hợp nhất vào `develop` và `master`.
  - Thêm core frontend (FE Admin Panel) và cập nhật README/docs.
- Cập nhật tài liệu dự án: README, hướng dẫn sử dụng, ảnh minh họa.

## 2025-11-15

- Merge `develop` vào `master` (PR #7) ổn định kiến trúc microservice và tài liệu đi kèm.

## 2025-11-11

- Merge `feature/microservice` vào `master`: bổ sung nền tảng microservices.
- Hoàn thiện FE Admin Panel đầu tiên.

## 2025-11-07

- Cập nhật assets hình ảnh phục vụ document/FE (`fix: add img`).

## 2025-11-06

- Khởi tạo dự án (các commit `Init`).
- Thêm license, changelog, code of conduct, contributing guide.
- Merge `feature/laravel` và `feat/document` vào master để hoàn thiện nền tảng Laravel ban đầu.
