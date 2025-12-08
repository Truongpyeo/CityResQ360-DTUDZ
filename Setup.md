# � Hướng dẫn cài đặt CityResQ360

> Hướng dẫn nhanh để cài đặt và chạy hệ thống CityResQ360 trên máy local

---

## � Yêu cầu hệ thống

### Nếu dùng Docker (Khuyến nghị ⭐)

| Công nghệ          | Phiên bản | Ghi chú                                                       |
| ------------------ | --------- | ------------------------------------------------------------- |
| **Docker**         | 20.10+    | [Tải tại đây](https://www.docker.com/products/docker-desktop) |
| **Docker Compose** | 2.0+      | Đi kèm với Docker Desktop                                     |
| **Git**            | 2.30+     | [Tải tại đây](https://git-scm.com/downloads)                  |

> 💡 **Lưu ý**: Khi dùng Docker, bạn **KHÔNG CẦN** cài PHP, Node.js, Python, Go, MySQL, PostgreSQL... Tất cả đã có sẵn trong containers!

### Nếu KHÔNG dùng Docker

Xem hướng dẫn chi tiết tại: [docs/BUILD_WITHOUT_DOCKER.md](docs/BUILD_WITHOUT_DOCKER.md)

| Công nghệ    | Phiên bản | Mục đích                   |
| ------------ | --------- | -------------------------- |
| **PHP**      | 8.2+      | Laravel Core API           |
| **Node.js**  | 20.0+     | Microservices & Mobile App |
| **Python**   | 3.10+     | AI/ML Services             |
| **Go**       | 1.21+     | High-performance services  |
| **Composer** | 2.0+      | PHP package manager        |
| **npm/yarn** | Latest    | Node.js package manager    |

---

## ⚡ Cài đặt nhanh

### 🚀 Cài đặt nhanh với Docker

**Yêu cầu**: Docker, Docker Compose, Git

#### **Cách 1: Dùng script tự động (Khuyến nghị)**

**Linux/macOS:**

```bash
# 1. Clone repository
git clone https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ.git
cd CityResQ360-DTUDZ

# 2. Chạy script quản lý
chmod +x scripts/local/run.sh
./scripts/local/run.sh

# Menu sẽ hiện:
# 1) Start all services       - Khởi động tất cả
# 2) Stop all services        - Dừng tất cả
# 3) Restart all services     - Khởi động lại
# 4) Clean rebuild            - Xóa và build lại từ đầu
# 5) View logs               - Xem logs
# 6) Check status            - Kiểm tra trạng thái
# 7) Run migrations          - Chạy database migrations
# 8) Test endpoints          - Test API endpoints
```

**Windows:**

_Cách 1 - Git Bash (Khuyến nghị):_

```bash
# 1. Clone repository
git clone https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ.git
cd CityResQ360-DTUDZ

# 2. Right-click trong folder → "Git Bash Here"

# 3. Fix line endings nếu cần
sed -i 's/\r$//' scripts/local/run.sh

# 4. Chạy script
chmod +x scripts/local/run.sh
./scripts/local/run.sh
```

_Cách 2 - PowerShell/CMD:_

```powershell
# 1. Clone repository
git clone https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ.git
cd CityResQ360-DTUDZ

# 2. Chạy Docker Compose trực tiếp
cd infrastructure/docker
docker compose up -d

# 3. Chạy migrations
docker exec -it cityresq-coreapi php artisan migrate --seed
docker exec -it cityresq-coreapi php artisan key:generate
docker exec -it cityresq-coreapi php artisan config:cache
```

#### **Cách 2: Chạy thủ công**

````bash
# 1. Clone repository
git clone https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ.git
cd CityResQ360-DTUDZ

# 2. Khởi động databases trước
cd infrastructure/docker
docker compose up -d mysql postgres redis mongodb rabbitmq minio

# 3. Đợi 20 giây cho databases khởi động
sleep 20

# 4. Khởi động application services
docker compose up -d coreapi media-service iot-service incident-service \
    aiml-service analytics-service search-service floodeye-service

# 5. Chạy migrations
docker exec -it cityresq-coreapi php artisan migrate --seed
docker exec -it cityresq-coreapi php artisan key:generate
docker exec -it cityresq-coreapi php artisan config:cache

## 🔧 Cấu hình bổ sung

### 1. Tạo MinIO Bucket (Lưu trữ ảnh/video)

**Qua Web UI:**

1. Truy cập: http://localhost:9001
2. Đăng nhập: `minioadmin` / `minioadmin`
3. Tạo bucket tên: `cityresq-media`

**Qua Command Line:**

```bash
docker run --rm -it --network infrastructure_cityresq-network \
  minio/mc alias set myminio http://minio:9000 minioadmin minioadmin

docker run --rm -it --network infrastructure_cityresq-network \
  minio/mc mb myminio/cityresq-media
````

### 2. Khởi tạo PostgreSQL Extensions

```bash
# WalletService
docker exec -it cityresq-postgres psql -U cityresq -d wallet_db \
  -c "CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";"

# FloodEyeService (PostGIS)
docker exec -it cityresq-postgres-floodeye psql -U cityresq -d floodeye_db \
  -c "CREATE EXTENSION IF NOT EXISTS postgis;"

# IoTService (TimescaleDB)
docker exec -it cityresq-timescaledb psql -U cityresq -d iot_db \
  -c "CREATE EXTENSION IF NOT EXISTS timescaledb;"
```

---

## 🌐 Truy cập Services

| Service           | URL                                     | Credentials                  |
| ----------------- | --------------------------------------- | ---------------------------- |
| **CoreAPI**       | http://localhost:8000                   | -                            |
| **API Docs**      | http://localhost:8000/api/documentation | -                            |
| **Admin Panel**   | http://localhost:8000/admin             | admin@master.com / 123456    |
| **MinIO Console** | http://localhost:9001                   | minioadmin / minioadmin      |
| **RabbitMQ**      | http://localhost:15672                  | cityresq / cityresq_password |
| **OpenSearch**    | http://localhost:5601                   | -                            |
| **Grafana**       | http://localhost:3001                   | admin / admin                |

---

## ✅ Kiểm tra hệ thống

### Test API Endpoints

```bash
# CoreAPI
curl http://localhost:8000/api/health

# MediaService
curl http://localhost:8002/health

# IncidentService
curl http://localhost:8001/health

# AIMLService
curl http://localhost:8003/health
```

### Kiểm tra Database

```bash
# MySQL
docker exec -it cityresq-mysql mysql -u cityresq -pcityresq_password -e "SHOW DATABASES;"

# PostgreSQL
docker exec -it cityresq-postgres psql -U cityresq -d wallet_db -c "\dt"

# MongoDB
docker exec -it cityresq-mongodb mongosh -u cityresq -p cityresq_password \
  --authenticationDatabase admin --eval "show dbs"

# Redis
docker exec -it cityresq-redis redis-cli ping
# Kết quả: PONG
```

---

## 📞 Hỗ trợ

Gặp vấn đề? Liên hệ:

- **GitHub Issues**: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/issues
- **Email Team**:
  - Lê Thanh Trường: thanhtruong23111999@gmail.com
  - Nguyễn Văn Nhân: vannhan130504@gmail.com
  - Nguyễn Ngọc Duy Thái: kkdn011@gmail.com

---

---

## 👨‍💻 Dành cho Developer (Development không dùng Docker)

> Hướng dẫn setup môi trường phát triển local cho developer

### Yêu cầu

| Công nghệ    | Phiên bản | Cài đặt                                |
| ------------ | --------- | -------------------------------------- |
| **PHP**      | 8.2+      | https://www.php.net/downloads          |
| **Node.js**  | 20.0+     | https://nodejs.org/                    |
| **Python**   | 3.10+     | https://www.python.org/downloads/      |
| **Go**       | 1.21+     | https://go.dev/dl/                     |
| **Composer** | 2.0+      | https://getcomposer.org/download/      |
| **MySQL**    | 8.0+      | https://dev.mysql.com/downloads/mysql/ |
| **Redis**    | 6.0+      | https://redis.io/download              |

### Cài đặt Dependencies

```bash
# CoreAPI (Laravel)
cd modules/CoreAPI
composer install
npm install
cp .env.example .env
php artisan key:generate

# Mobile App
cd modules/AppMobile
npm install

# Node.js Services
cd modules/MediaService && npm install
cd modules/NotificationService && npm install
cd modules/IncidentService && npm install

# Python Services
cd modules/AIMLService
python -m venv venv
source venv/bin/activate  # Linux/macOS
venv\Scripts\activate     # Windows
pip install -r requirements.txt
```

### Cấu hình Database

```sql
-- MySQL
CREATE DATABASE cityresq_db;
CREATE USER 'cityresq'@'localhost' IDENTIFIED BY 'cityresq_password';
GRANT ALL PRIVILEGES ON cityresq_db.* TO 'cityresq'@'localhost';
```

Cập nhật `modules/CoreAPI/.env`:

```env
DB_HOST=127.0.0.1
DB_DATABASE=cityresq_db
DB_USERNAME=cityresq
DB_PASSWORD=cityresq_password
```

Chạy migrations:

```bash
cd modules/CoreAPI
php artisan migrate --seed
```

### Chạy Services

Mở terminal riêng cho mỗi service:

```bash
# Terminal 1: CoreAPI
cd modules/CoreAPI
php artisan serve

# Terminal 2: Vite (Frontend)
cd modules/CoreAPI
npm run dev

# Terminal 3: Queue Worker
cd modules/CoreAPI
php artisan queue:work

# Terminal 4: MediaService
cd modules/MediaService
npm run dev

# Terminal 5: AIMLService
cd modules/AIMLService
source venv/bin/activate
uvicorn main:app --reload --port 8008
```

### Testing

```bash
# Laravel
cd modules/CoreAPI
php artisan test

# Node.js
cd modules/MediaService
npm test

# Python
cd modules/AIMLService
pytest
```

### Debugging

**VS Code**: Cài extensions PHP Debug, Python, ESLint

**Xdebug** (PHP): Thêm vào `php.ini`:

```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
```

---

## � React Native Development (Mobile App)

### Yêu cầu bổ sung

| Platform    | Yêu cầu                                         |
| ----------- | ----------------------------------------------- |
| **iOS**     | macOS, Xcode 14+, CocoaPods                     |
| **Android** | Android Studio, JDK 17+, Android SDK (API 33+)  |
| **Chung**   | Node.js 20+, React Native CLI, Watchman (macOS) |

### Cài đặt môi trường

#### macOS (iOS + Android)

```bash
# Cài đặt Homebrew (nếu chưa có)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Cài đặt Node.js và Watchman
brew install node
brew install watchman

# Cài đặt CocoaPods (cho iOS)
sudo gem install cocoapods

# Cài đặt React Native CLI
npm install -g react-native-cli
```

**Xcode**: Tải từ App Store

**Android Studio**: Tải từ https://developer.android.com/studio

#### Windows (Android only)

```powershell
# Cài đặt Node.js từ https://nodejs.org/

# Cài đặt React Native CLI
npm install -g react-native-cli

# Cài đặt JDK 17
# Tải từ https://www.oracle.com/java/technologies/downloads/
```

**Android Studio**: Tải từ https://developer.android.com/studio

#### Linux (Android only)

```bash
# Cài đặt Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Cài đặt JDK
sudo apt install openjdk-17-jdk

# Cài đặt React Native CLI
npm install -g react-native-cli
```

**Android Studio**: Tải từ https://developer.android.com/studio

### Setup Mobile App

```bash
cd modules/AppMobile

# Cài đặt dependencies
npm install
# hoặc
yarn install

# iOS only (macOS)
cd ios
pod install
cd ..
```

### Cấu hình Android

Thêm vào `~/.bashrc` hoặc `~/.zshrc` (Linux/macOS) hoặc Environment Variables (Windows):

```bash
export ANDROID_HOME=$HOME/Android/Sdk
export PATH=$PATH:$ANDROID_HOME/emulator
export PATH=$PATH:$ANDROID_HOME/platform-tools
```

### Chạy Mobile App

#### iOS (macOS only)

```bash
cd modules/AppMobile

# Chạy trên simulator
npm run ios

# Chạy trên device cụ thể
npm run ios -- --simulator="iPhone 15 Pro"

# Hoặc mở Xcode
open ios/CityResQ360App.xcworkspace
```

#### Android

```bash
cd modules/AppMobile

# Khởi động emulator trước (hoặc kết nối device thật)

# Chạy app
npm run android

# Hoặc mở Android Studio
# File → Open → chọn thư mục android/
```

### Development Tips

```bash
# Start Metro bundler
npm start

# Clear cache nếu gặp lỗi
npm start -- --reset-cache

# Rebuild app
cd android && ./gradlew clean && cd ..
npm run android

# iOS rebuild
cd ios && pod install && cd ..
npm run ios
```

### Debug

- **React Native Debugger**: https://github.com/jhen0409/react-native-debugger
- **Flipper**: https://fbflipper.com/
- **Chrome DevTools**: Shake device → Debug

### Common Issues

**Metro bundler error**:

```bash
npm start -- --reset-cache
```

**iOS build failed**:

```bash
cd ios
pod deintegrate
pod install
cd ..
```

**Android build failed**:

```bash
cd android
./gradlew clean
cd ..
```

---

## �📚 Tài liệu thêm

- [README.md](README.md) - Tổng quan dự án
- [CONTRIBUTING.md](CONTRIBUITING.md) - Hướng dẫn đóng góp
- [Documentation](https://nguyenthai11103.github.io/DTU-CityResQ360-documents/) - Tài liệu chi tiết
- [BUILD_WITHOUT_DOCKER.md](docs/BUILD_WITHOUT_DOCKER.md) - Cài đặt không dùng Docker

---

**Chúc bạn cài đặt thành công! 🎉**

© 2025 CityResQ360 – DTU-DZ Team
