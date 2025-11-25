#!/bin/bash

# ============================================
# Script Deploy CityResQ360 lên VPS
# ============================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables - THAY ĐỔI CÁC GIÁ TRỊ NÀY
DOMAIN="midstack.io.vn"
EMAIL="thanhtruong23111999@gmail.com"  # Email để nhận thông báo từ Let's Encrypt

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}CityResQ360 - Production Deployment${NC}"
echo -e "${GREEN}========================================${NC}"

# Kiểm tra quyền root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Vui lòng chạy script với quyền root (sudo)${NC}"
    exit 1
fi

# Cập nhật hệ thống
echo -e "${YELLOW}[1/8] Cập nhật hệ thống...${NC}"
apt-get update
apt-get upgrade -y

# Cài đặt Docker và Docker Compose
echo -e "${YELLOW}[2/8] Cài đặt Docker và Docker Compose...${NC}"
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
    systemctl enable docker
    systemctl start docker
fi

if ! command -v docker-compose &> /dev/null; then
    curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
fi

# Cài đặt Nginx
echo -e "${YELLOW}[3/8] Cài đặt Nginx...${NC}"
if ! command -v nginx &> /dev/null; then
    apt-get install -y nginx
    systemctl enable nginx
    systemctl start nginx
fi

# Cài đặt Certbot (Let's Encrypt)
echo -e "${YELLOW}[4/8] Cài đặt Certbot...${NC}"
if ! command -v certbot &> /dev/null; then
    apt-get install -y certbot python3-certbot-nginx
fi

# Tạo thư mục project
PROJECT_DIR="/opt/cityresq360"
echo -e "${YELLOW}[5/8] Tạo thư mục project tại $PROJECT_DIR...${NC}"
mkdir -p $PROJECT_DIR

# Copy file nginx config
echo -e "${YELLOW}[6/8] Cấu hình Nginx...${NC}"
if [ -f "./nginx/nginx.conf" ]; then
    # Thay thế yourdomain.com trong file nginx config
    sed "s/yourdomain.com/$DOMAIN/g" ./nginx/nginx.conf > /etc/nginx/sites-available/cityresq360
    
    # Tạo symlink
    ln -sf /etc/nginx/sites-available/cityresq360 /etc/nginx/sites-enabled/
    
    # Xóa default config
    rm -f /etc/nginx/sites-enabled/default
    
    # Test nginx config
    nginx -t
    
    # Reload nginx
    systemctl reload nginx
else
    echo -e "${RED}Không tìm thấy file nginx/nginx.conf${NC}"
    exit 1
fi

# Tạo file .env cho production
echo -e "${YELLOW}[7/8] Tạo file .env production...${NC}"

# Generate random passwords (alphanumeric only để tránh lỗi với shell và connection strings)
MYSQL_ROOT_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32)
MYSQL_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32)
MONGODB_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32)
POSTGRES_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32)
CLICKHOUSE_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32)
RABBITMQ_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32)
MINIO_ROOT_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32)
# JWT_SECRET dùng alphanumeric
JWT_SECRET=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 64)

cat > $PROJECT_DIR/.env << EOF
# ============================================
# Laravel Application Config
# ============================================
APP_NAME=CityResQ360
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Ho_Chi_Minh
APP_URL=https://api.$DOMAIN
APP_LOCALE=vi
APP_FALLBACK_LOCALE=vi

# ============================================
# Laravel Database (MySQL)
# ============================================
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cityresq_db
DB_USERNAME=cityresq
DB_PASSWORD=${MYSQL_PASSWORD}

# ============================================
# Database Passwords (Docker)
# ============================================
MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
MYSQL_PASSWORD=${MYSQL_PASSWORD}
MONGODB_PASSWORD=${MONGODB_PASSWORD}
POSTGRES_PASSWORD=${POSTGRES_PASSWORD}
CLICKHOUSE_PASSWORD=${CLICKHOUSE_PASSWORD}

# ============================================
# Redis Cache
# ============================================
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1

# ============================================
# Queue & Broadcasting
# ============================================
QUEUE_CONNECTION=rabbitmq
BROADCAST_CONNECTION=rabbitmq
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=cityresq
RABBITMQ_PASSWORD=${RABBITMQ_PASSWORD}
RABBITMQ_VHOST=/
RABBITMQ_QUEUE=default

# ============================================
# Session & Cache
# ============================================
SESSION_DRIVER=redis
SESSION_LIFETIME=120
CACHE_STORE=redis

# ============================================
# File Storage (MinIO S3-compatible)
# ============================================
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=${MINIO_ROOT_PASSWORD}
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=cityresq
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_URL=https://media.$DOMAIN

MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=${MINIO_ROOT_PASSWORD}

# ============================================
# JWT Authentication
# ============================================
JWT_SECRET=${JWT_SECRET}
JWT_TTL=60
JWT_REFRESH_TTL=20160

# ============================================
# Microservices URLs (Internal Docker)
# ============================================
MEDIA_SERVICE_URL=http://media-service:8004/api/v1
NOTIFICATION_SERVICE_URL=http://notification-service:8006/api/v1
WALLET_SERVICE_URL=http://wallet-service:8005/api/v1
INCIDENT_SERVICE_URL=http://incident-service:8001/api/v1
IOT_SERVICE_URL=http://iot-service:8002/api/v1
AIML_SERVICE_URL=http://aiml-service:8003/api/v1
SEARCH_SERVICE_URL=http://search-service:8007/api/v1
FLOODEYE_SERVICE_URL=http://floodeye-service:8008/api/v1
ANALYTICS_SERVICE_URL=http://analytics-service:8009/api/v1

# ============================================
# Public URLs (cho Frontend/Client)
# ============================================
NEXT_PUBLIC_API_URL=https://api.$DOMAIN/api/v1
NEXT_PUBLIC_MEDIA_URL=https://media.$DOMAIN
NEXT_PUBLIC_WS_URL=wss://api.$DOMAIN

# ============================================
# MQTT
# ============================================
MQTT_HOST=mqtt
MQTT_PORT=1883
MQTT_USERNAME=
MQTT_PASSWORD=

# ============================================
# MongoDB (cho Media Service)
# ============================================
MONGODB_HOST=mongodb
MONGODB_PORT=27017
MONGODB_USERNAME=cityresq
MONGODB_PASSWORD=${MONGODB_PASSWORD}
MONGODB_DATABASE=media_db

# ============================================
# Mail Configuration (optional - update if needed)
# ============================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@$DOMAIN
MAIL_FROM_NAME="\${APP_NAME}"

# ============================================
# Logging
# ============================================
LOG_CHANNEL=daily
LOG_LEVEL=info
LOG_DEPRECATIONS_CHANNEL=null

# ============================================
# Services Config (optional)
# ============================================
# FCM_PROJECT_ID=
# FCM_CLIENT_EMAIL=
# FCM_PRIVATE_KEY=
EOF

echo -e "${GREEN}File .env đã được tạo tại $PROJECT_DIR/.env${NC}"
echo -e "${YELLOW}Vui lòng kiểm tra và cập nhật các giá trị trong file này!${NC}"

# Hướng dẫn cấu hình DNS
echo -e "${YELLOW}[8/8] Hướng dẫn cấu hình DNS...${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Cấu hình DNS records:${NC}"
echo -e "${GREEN}========================================${NC}"
echo "A     @              -> $(curl -s ifconfig.me)"
echo "A     www            -> $(curl -s ifconfig.me)"
echo "A     api            -> $(curl -s ifconfig.me)"
echo "A     media          -> $(curl -s ifconfig.me)"
echo "A     notification   -> $(curl -s ifconfig.me)"
echo "A     wallet         -> $(curl -s ifconfig.me)"
echo "A     incident       -> $(curl -s ifconfig.me)"
echo "A     iot            -> $(curl -s ifconfig.me)"
echo "A     aiml           -> $(curl -s ifconfig.me)"
echo "A     search         -> $(curl -s ifconfig.me)"
echo "A     floodeye       -> $(curl -s ifconfig.me)"
echo "A     analytics      -> $(curl -s ifconfig.me)"
echo -e "${GREEN}========================================${NC}"

echo -e "${YELLOW}Chờ DNS propagate (có thể mất vài phút đến vài giờ)...${NC}"
read -p "Nhấn Enter khi đã cấu hình DNS xong..."

# Cấp SSL certificate cho tất cả subdomains
echo -e "${YELLOW}Cấp SSL certificates...${NC}"

# Main domain
certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# API domain
certbot --nginx -d api.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# Media domain
certbot --nginx -d media.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# Notification domain
certbot --nginx -d notification.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# Wallet domain
certbot --nginx -d wallet.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# Incident domain
certbot --nginx -d incident.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# IoT domain
certbot --nginx -d iot.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# AI/ML domain
certbot --nginx -d aiml.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# Search domain
certbot --nginx -d search.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# FloodEye domain
certbot --nginx -d floodeye.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# Analytics domain
certbot --nginx -d analytics.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect

# Cập nhật nginx config với SSL paths
echo -e "${YELLOW}Cập nhật nginx config với SSL paths...${NC}"
# Certbot đã tự động cập nhật, chỉ cần reload
systemctl reload nginx

# Copy project files
echo -e "${YELLOW}Copy project files...${NC}"
# Copy toàn bộ project
mkdir -p $PROJECT_DIR
cp -r . $PROJECT_DIR/

# Xóa các thư mục không cần thiết
echo -e "${YELLOW}Dọn dẹp thư mục không cần thiết...${NC}"
rm -rf $PROJECT_DIR/.git
find $PROJECT_DIR -type d -name "node_modules" -exec rm -rf {} + 2>/dev/null || true
find $PROJECT_DIR -type d -name "vendor" -exec rm -rf {} + 2>/dev/null || true
find $PROJECT_DIR -type d -name ".next" -exec rm -rf {} + 2>/dev/null || true

# Di chuyển vào thư mục project
cd $PROJECT_DIR

# Đảm bảo file .env có trong thư mục CoreAPI (Laravel cần)
echo -e "${YELLOW}Tạo symlink .env cho CoreAPI...${NC}"
ln -sf $PROJECT_DIR/.env $PROJECT_DIR/CoreAPI/.env

# Ensure go.sum exists for wallet-service to avoid Docker build issues
if [ ! -f "WalletService/go.sum" ]; then
    echo -e "${YELLOW}WalletService: tạo placeholder go.sum...${NC}"
    touch WalletService/go.sum
fi

# Build và start Docker containers
echo -e "${YELLOW}Build và khởi động Docker containers...${NC}"

# Kiểm tra xem có containers đang chạy không
if docker ps -a --format '{{.Names}}' | grep -q 'cityresq-'; then
    echo -e "${YELLOW}Phát hiện containers cũ đang chạy...${NC}"
    read -p "Bạn có muốn down containers cũ trước khi rebuild? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Dừng và xóa containers cũ (giữ nguyên volumes)...${NC}"
        docker-compose -f docker-compose.production.yml down
    fi
fi

docker-compose -f docker-compose.production.yml --env-file .env up -d --build

# Chờ các database services khởi động trước
echo -e "${YELLOW}Chờ database services khởi động...${NC}"
sleep 20

# Generate Laravel APP_KEY trong container
echo -e "${YELLOW}Generate Laravel APP_KEY...${NC}"
docker exec cityresq-coreapi php artisan key:generate --force

# Chạy Laravel migrations
echo -e "${YELLOW}Chạy Laravel migrations...${NC}"
docker exec cityresq-coreapi php artisan migrate --force

# Chạy Laravel seeders (nếu cần)
echo -e "${YELLOW}Chạy Laravel seeders...${NC}"
docker exec cityresq-coreapi php artisan db:seed --force --class=AdminSeeder || true

# Tối ưu Laravel
echo -e "${YELLOW}Tối ưu Laravel cache...${NC}"
docker exec cityresq-coreapi php artisan config:cache
docker exec cityresq-coreapi php artisan route:cache
docker exec cityresq-coreapi php artisan view:cache

# Chờ các services còn lại khởi động
echo -e "${YELLOW}Chờ các services còn lại khởi động...${NC}"
sleep 20

# Kiểm tra status
echo -e "${YELLOW}Kiểm tra trạng thái services...${NC}"
docker-compose -f docker-compose.production.yml ps

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Deployment hoàn tất!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}URLs:${NC}"
echo -e "  Main App:     https://$DOMAIN"
echo -e "  API:          https://api.$DOMAIN"
echo -e "  Admin Panel:  https://api.$DOMAIN/admin"
echo -e "  Media:        https://media.$DOMAIN"
echo -e "${GREEN}========================================${NC}"

# Auto-renew SSL certificates
echo -e "${YELLOW}Thiết lập auto-renew SSL...${NC}"
(crontab -l 2>/dev/null; echo "0 0 * * * certbot renew --quiet --post-hook 'systemctl reload nginx'") | crontab -

echo -e "${GREEN}Hoàn tất!${NC}"

