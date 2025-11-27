# Build Without Docker

Hướng dẫn cài đặt và chạy CityResQ360 **KHÔNG sử dụng Docker** (bare metal).

## Yêu Cầu Hệ Thống

### Phần Mềm Bắt Buộc

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 20.x
- **npm** hoặc **yarn**
- **Python** >= 3.10
- **pip**
- **Go** >= 1.21
- **PostgreSQL** >= 15
- **Redis** >= 7.0
- **MongoDB** >= 6.0 (cho ContextBroker)

### Optional Services

- **Kafka** >= 3.0 (cho message broker)
- **Mosquitto** (MQTT broker)
- **MinIO** (object storage)

---

## 1. Cài Đặt Dependencies

### Ubuntu/Debian

```bash
# PHP
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-common \
  php8.2-mysql php8.2-pgsql php8.2-redis php8.2-curl \
  php8.2-mbstring php8.2-xml php8.2-zip php8.2-gd \
  php8.2-bcmath php8.2-intl

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Python
sudo apt install python3.10 python3-pip python3-venv

# Go
wget https://go.dev/dl/go1.21.0.linux-amd64.tar.gz
sudo tar -C /usr/local -xzf go1.21.0.linux-amd64.tar.gz
export PATH=$PATH:/usr/local/go/bin

# PostgreSQL
sudo apt install postgresql postgresql-contrib postgis

# Redis  
sudo apt install redis-server

# MongoDB
wget -qO - https://www.mongodb.org/static/pgp/server-6.0.asc | sudo apt-key add -
sudo apt install mongodb-org
```

### macOS

```bash
# Homebrew
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Services
brew install php composer node python postgresql@15 redis mongodb-community

# Go
brew install go
```

---

## 2. Cấu Hình Database

### PostgreSQL

```bash
# Start PostgreSQL
sudo systemctl start postgresql

# Create database và user
sudo -u postgres psql << EOF
CREATE DATABASE cityresq360;
CREATE USER cityresq_user WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE cityresq360 TO cityresq_user;

-- Enable PostGIS
\c cityresq360
CREATE EXTENSION postgis;
EOF
```

### Redis

```bash
sudo systemctl start redis
```

### MongoDB

```bash
sudo systemctl start mongod
```

---

## 3. CoreAPI (Laravel)

```bash
cd modules/CoreAPI

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate APP_KEY
php artisan key:generate

# Configure .env
nano .env
```

**Cấu hình .env:**
```env
APP_NAME=CityResQ360
APP_ENV=production
APP_KEY=base64:xxx  # Generated automatically
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cityresq360
DB_USERNAME=cityresq_user
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Run migrations:**
```bash
php artisan migrate --seed
php artisan storage:link

# Start server
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 4. AI/ML Service (FastAPI)

```bash
cd modules/AIMLService

# Create virtual environment
python3 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Run service
uvicorn main:app --host 0.0.0.0 --port 8003 --reload
```

---

## 5. IoT Service (Node.js)

```bash
cd modules/IoTService

# Install dependencies
npm install

# Configure environment
cp .env.example .env
nano .env
```

**.env:**
```env
PORT=8002
MQTT_BROKER=mqtt://localhost:1883
MONGODB_URI=mongodb://localhost:27017/cityresq360_iot
```

**Run:**
```bash
npm start
# OR for development:
npm run dev
```

---

## 6. Media Service (Node.js)

```bash
cd modules/MediaService

npm install
cp .env.example .env

# Configure MinIO or use local storage
nano .env
```

**Run:**
```bash
npm start
```

---

## 7. Other Services

Repeat similar steps for:
- **NotificationService** (port 8006)
- **IncidentService** (port 8001)
- **SearchService** (port 8007)
- **AnalyticsService** (port 8009)
- **WalletService** (Go - port 8005)
- **FloodEyeService** (port 8008)

### WalletService (Go)

```bash
cd modules/WalletService

# Install dependencies
go mod download

# Build
go build -o wallet-service cmd/server/main.go

# Run
./wallet-service
# OR directly:
go run cmd/server/main.go
```

---

## 8. Message Brokers (Optional)

### Kafka

```bash
# Download Kafka
wget https://downloads.apache.org/kafka/3.6.0/kafka_2.13-3.6.0.tgz
tar -xzf kafka_2.13-3.6.0.tgz
cd kafka_2.13-3.6.0

# Start Zookeeper
bin/zookeeper-server-start.sh config/zookeeper.properties &

# Start Kafka
bin/kafka-server-start.sh config/server.properties &
```

### Mosquitto (MQTT)

```bash
sudo apt install mosquitto mosquitto-clients
sudo systemctl start mosquitto
```

---

## 9. Verify Services

Check all services running:

```bash
# CoreAPI
curl http://localhost:8000/api/v1/health

# AI Service  
curl http://localhost:8003/health

# IoT Service
curl http://localhost:8002/health

# ... check others similarly
```

---

## 10. Production Deployment

### Using systemd

Create service file `/etc/systemd/system/cityresq-core.service`:

```ini
[Unit]
Description=CityResQ360 Core API
After=network.target postgresql.service redis.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/cityresq360/modules/CoreAPI
ExecStart=/usr/bin/php artisan serve --host=0.0.0.0 --port=8000
Restart=always

[Install]
WantedBy=multi-user.target
```

Enable và start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable cityresq-core
sudo systemctl start cityresq-core
```

Repeat for other services.

### Using PM2 (for Node.js services)

```bash
# Install PM2
npm install -g pm2

# Start services
pm2 start modules/IoTService/index.js --name iot-service
pm2 start modules/MediaService/index.js --name media-service
# ... others

# Save configuration
pm2 save
pm2 startup
```

---

## 11. Nginx Reverse Proxy

**File: `/etc/nginx/sites-available/cityresq360`**

```nginx
server {
    listen 80;
    server_name your-domain.com;

    # CoreAPI
    location /api/ {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # WebSocket (Reverb)
    location /ws {
        proxy_pass http://localhost:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
    
    # Static files
    location /storage/ {
        alias /var/www/cityresq360/modules/CoreAPI/storage/app/public/;
    }
}
```

Enable:
```bash
sudo ln -s /etc/nginx/sites-available/cityresq360 /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## Troubleshooting

### PHP Extensions Missing

```bash
sudo apt install php8.2-{extension-name}
sudo systemctl restart php8.2-fpm
```

### PostgreSQL Connection Error

```bash
sudo -u postgres psql
\du  # List users
\l   # List databases
```

### Port Already in Use

```bash
# Find process
sudo lsof -i :8000

# Kill
kill -9 <PID>
```

---

## Performance Tuning

### PHP-FPM

Edit `/etc/php/8.2/fpm/pool.d/www.conf`:
```ini
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
```

### PostgreSQL

Edit `/etc/postgresql/15/main/postgresql.conf`:
```
shared_buffers = 256MB
effective_cache_size = 1GB
work_mem = 16MB
```

---

## Môi Trường Development

```bash
# Install nodemon for auto-reload
npm install -g nodemon

# Use in Node services
nodemon index.js

# Laravel auto-reload
php artisan serve --host=0.0.0.0 --port=8000

# Python auto-reload (uvicorn đã có --reload)
```

---

**Lưu ý:** Build production nên dùng Docker để đảm bảo consistency. Hướng dẫn này chỉ cho development hoặc khi không có Docker.
