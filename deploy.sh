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
cat > $PROJECT_DIR/.env << EOF
# Domain Configuration
APP_URL=https://api.$DOMAIN
NEXT_PUBLIC_API_URL=https://api.$DOMAIN/api/v1

# Database Passwords - THAY ĐỔI CÁC MẬT KHẨU NÀY
MYSQL_ROOT_PASSWORD="$(openssl rand -base64 32)"
MYSQL_PASSWORD="$(openssl rand -base64 32)"
MONGODB_PASSWORD="$(openssl rand -base64 32)"
POSTGRES_PASSWORD="$(openssl rand -base64 32)"
CLICKHOUSE_PASSWORD="$(openssl rand -base64 32)"
RABBITMQ_PASSWORD="$(openssl rand -base64 32)"
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD="$(openssl rand -base64 32)"

# JWT Secret - THAY ĐỔI
JWT_SECRET="$(openssl rand -base64 64)"

# Service URLs
MEDIA_SERVICE_URL=https://media.$DOMAIN/api/v1
NOTIFICATION_SERVICE_URL=https://notification.$DOMAIN/api/v1
WALLET_SERVICE_URL=https://wallet.$DOMAIN/api/v1

# CDN URL (optional)
# CDN_URL=https://cdn.$DOMAIN

# SMTP Configuration (tùy chọn)
# SMTP_HOST=smtp.gmail.com
# SMTP_PORT=587
# SMTP_USER=your-email@gmail.com
# SMTP_PASS=your-app-password
# SMTP_FROM=noreply@$DOMAIN

# Firebase Cloud Messaging (tùy chọn)
# FCM_PROJECT_ID=your-project-id
# FCM_CLIENT_EMAIL=your-client-email
# FCM_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"
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
cp -r . $PROJECT_DIR/
cd $PROJECT_DIR

# Build và start Docker containers
echo -e "${YELLOW}Build và khởi động Docker containers...${NC}"
docker-compose -f docker-compose.production.yml --env-file .env up -d --build

# Chờ services khởi động
echo -e "${YELLOW}Chờ services khởi động...${NC}"
sleep 30

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

