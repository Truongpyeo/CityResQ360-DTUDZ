# 🐳 DOCKER DEPLOYMENT GUIDE - CITYRESQ360

Hướng dẫn deploy CityResQ360 với Docker cho 2 môi trường:
- **Development:** Docker Desktop (Mac/Windows)
- **Production:** VPS Ubuntu với Docker

---

## 📋 MỤC LỤC

1. [Tổng Quan Hệ Thống](#tổng-quan-hệ-thống)
2. [Development - Docker Desktop](#development-docker-desktop)
3. [Production - VPS Ubuntu](#production-vps-ubuntu)
4. [Quản Lý Services](#quản-lý-services)
5. [Troubleshooting](#troubleshooting)

---

## 🏗️ TỔNG QUAN HỆ THỐNG

### Kiến Trúc Microservices

**11 Application Services:**

| Service | Port | Tech | Database |
|---------|------|------|----------|
| CoreAPI | 8000 | Laravel 12 | MySQL:3307 |
| IncidentService | 8001 | Node.js | PostgreSQL:5434 |
| IoTService | 8002 | Node.js | TimescaleDB:5433 |
| AIMLService | 8003 | Python | PostgreSQL:5435 |
| MediaService | 8004 | Node.js | MongoDB:27017 |
| WalletService | 8005 | Go | PostgreSQL:5432 |
| NotificationService | 8006 | Node.js | MongoDB:27017 |
| SearchService | 8007 | Python | OpenSearch:9200 |
| FloodEyeService | 8008 | Python | PostGIS:5436 |
| AnalyticsService | 8009 | Python | ClickHouse:8123 |
| AppMobile | 3000 | Next.js | - |

**Infrastructure Services:**
- RabbitMQ (5672, 15672), MQTT (1883), Redis (6379)
- MinIO (9000, 9001), OpenSearch Dashboard (5601)

---

## 💻 DEVELOPMENT - DOCKER DESKTOP

### Yêu Cầu Hệ Thống

- **Docker Desktop:** 4.25+ (Mac/Windows)
- **RAM:** 8GB minimum, 16GB recommended
- **CPU:** 4 cores minimum
- **Disk:** 50GB free space

### Bước 1: Cài Đặt Docker Desktop

**Mac:**
```bash
# Download from: https://www.docker.com/products/docker-desktop/

# Verify installation
docker --version
docker-compose --version
```

**Windows:**
- Cài WSL 2 trước
- Download Docker Desktop for Windows
- Enable Hyper-V (nếu cần)

### Bước 2: Cấu Hình Resources

**Docker Desktop → Settings → Resources:**
- **CPUs:** 4-8 cores
- **Memory:** 8-16GB
- **Disk:** 50GB+
- **Swap:** 2GB

### Bước 3: Clone Project

```bash
cd /path/to/your/workspace
git clone <your-repo-url>
cd CityResQ360-DTUDZ
```

### Bước 4: Tạo .env File (Optional)

```bash
cd infrastructure/docker

# Create .env for custom passwords
cat > .env << 'EOF'
# Database Passwords
MYSQL_PASSWORD=your_secure_password
MONGODB_PASSWORD=your_secure_password
POSTGRES_PASSWORD=your_secure_password
RABBITMQ_PASSWORD=your_secure_password

# MinIO
MINIO_ROOT_USER=admin
MINIO_ROOT_PASSWORD=your_minio_password

# JWT
JWT_SECRET=your_jwt_secret_min_32_chars

# URLs
APP_URL=http://localhost:8000
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
EOF
```

### Bước 5: Start Services

#### Option A: Start Tất Cả (Full Stack)

```bash
cd infrastructure/docker

# Build và start tất cả services
docker-compose up -d --build

# Check status
docker-compose ps
```

#### Option B: Start Từng Nhóm (Recommended)

```bash
# 1. Start infrastructure
docker-compose up -d mysql mongodb postgres redis rabbitmq minio

# Wait 30 seconds

# 2. Start core services
docker-compose up -d coreapi media-service notification-service

# 3. Start additional services as needed
docker-compose up -d wallet-service iot-service
```

### Bước 6: Initialize Data (First Time)

```bash
# Run migrations
docker exec cityresq-coreapi php artisan migrate --force

# Seed data
docker exec cityresq-coreapi php artisan db:seed --force

# Create MinIO bucket
docker exec cityresq-minio mc alias set local http://localhost:9000 minioadmin minioadmin
docker exec cityresq-minio mc mb local/cityresq-media
docker exec cityresq-minio mc policy set download local/cityresq-media
```

### Bước 7: Access Services

**Web UIs:**
- CoreAPI: http://localhost:8000
- Admin Panel: http://localhost:8000/admin
- AppMobile: http://localhost:3000
- RabbitMQ: http://localhost:15672 (user: `cityresq`, pass: `cityresq_password`)
- MinIO Console: http://localhost:9001
- OpenSearch Dashboard: http://localhost:5601

**Database Connections:**
```bash
# MySQL
mysql -h 127.0.0.1 -P 3307 -u cityresq -p

# MongoDB
mongosh "mongodb://cityresq:cityresq_password@localhost:27017/?authSource=admin"

# PostgreSQL
psql -h localhost -p 5432 -U cityresq -d wallet_db
```

### Bước 8: View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f coreapi
docker-compose logs -f media-service

# Last 100 lines
docker-compose logs --tail=100 coreapi
```

### Bước 9: Stop Services

```bash
# Stop all (keep data)
docker-compose down

# Stop and remove volumes (DELETE DATA)
docker-compose down -v

# Restart specific service
docker-compose restart coreapi
```

---

## 🐧 PRODUCTION - VPS UBUNTU

### VPS Requirements

- **OS:** Ubuntu 22.04 LTS
- **RAM:** 16GB minimum
- **CPU:** 4+ cores
- **Disk:** 100GB SSD
- **Network:** Public IP + Domain name

### Bước 1: SSH vào VPS

```bash
# From local machine
ssh root@your-vps-ip

# Or with key
ssh -i ~/.ssh/your-key.pem ubuntu@your-vps-ip
```

### Bước 2: Update System

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git wget vim htop unzip
```

### Bước 3: Install Docker

```bash
# Install Docker
curl -fsSL https://get.docker.com | sudo sh

# Add user to docker group
sudo usermod -aG docker $USER
newgrp docker

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify
docker --version
docker-compose --version
```

### Bước 4: Clone Project

```bash
cd /opt
git clone https://github.com/your-repo/CityResQ360-DTUDZ.git
cd CityResQ360-DTUDZ/infrastructure/docker
```

### Bước 5: Create Production .env

```bash
# Generate strong passwords
cat > .env << 'EOF'
# PRODUCTION - USE STRONG PASSWORDS!
MYSQL_ROOT_PASSWORD=$(openssl rand -base64 32)
MYSQL_PASSWORD=$(openssl rand -base64 32)
MONGODB_PASSWORD=$(openssl rand -base64 32)
POSTGRES_PASSWORD=$(openssl rand -base64 32)
RABBITMQ_PASSWORD=$(openssl rand -base64 32)
CLICKHOUSE_PASSWORD=$(openssl rand -base64 32)

MINIO_ROOT_USER=admin
MINIO_ROOT_PASSWORD=$(openssl rand -base64 32)

JWT_SECRET=$(openssl rand -base64 64)

# Domain (change to yours)
APP_URL=https://api.yourdomain.com
NEXT_PUBLIC_API_URL=https://api.yourdomain.com/api/v1

# Firebase FCM
FCM_PROJECT_ID=your-project
FCM_CLIENT_EMAIL=firebase@your-project.iam.gserviceaccount.com
FCM_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"

# SMTP
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=noreply@yourdomain.com
SMTP_PASS=your-app-password
SMTP_FROM=noreply@yourdomain.com
EOF

# Secure the file
chmod 600 .env
```

### Bước 6: Configure Firewall

```bash
# Allow SSH, HTTP, HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow service ports (optional - if direct access needed)
sudo ufw allow 8000:8009/tcp
sudo ufw allow 3000/tcp
sudo ufw allow 15672/tcp  # RabbitMQ Management
sudo ufw allow 9001/tcp   # MinIO Console

# Enable firewall
sudo ufw enable
sudo ufw status
```

### Bước 7: Build & Start Services

```bash
cd /opt/CityResQ360-DTUDZ/infrastructure/docker

# Build all images
docker-compose -f docker-compose.production.yml build

# Start all services
docker-compose -f docker-compose.production.yml up -d

# Check status
docker-compose -f docker-compose.production.yml ps
```

### Bước 8: Setup Nginx Reverse Proxy

```bash
# Install Nginx
sudo apt install -y nginx

# Create config
sudo nano /etc/nginx/sites-available/cityresq360
```

Paste config:

```nginx
# API Domain
server {
    listen 80;
    server_name api.yourdomain.com;

    client_max_body_size 100M;

    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 300s;
    }
}

# App Domain
server {
    listen 80;
    server_name app.yourdomain.com;

    location / {
        proxy_pass http://localhost:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}

# RabbitMQ Management
server {
    listen 80;
    server_name rabbitmq.yourdomain.com;

    location / {
        proxy_pass http://localhost:15672;
        proxy_set_header Host $host;
    }
}

# MinIO Console
server {
    listen 80;
    server_name minio.yourdomain.com;

    location / {
        proxy_pass http://localhost:9001;
        proxy_set_header Host $host;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/cityresq360 /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Bước 9: Setup SSL (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get certificates for each domain
sudo certbot --nginx -d api.yourdomain.com
sudo certbot --nginx -d app.yourdomain.com
sudo certbot --nginx -d rabbitmq.yourdomain.com
sudo certbot --nginx -d minio.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### Bước 10: Initialize Production Data

```bash
cd /opt/CityResQ360-DTUDZ/infrastructure/docker

# Run migrations
docker exec cityresq-coreapi php artisan migrate --force

# Seed admin user
docker exec cityresq-coreapi php artisan db:seed --class=AdminSeeder

# Setup MinIO (use your password from .env)
docker exec cityresq-minio mc alias set local http://localhost:9000 admin <MINIO_ROOT_PASSWORD>
docker exec cityresq-minio mc mb local/cityresq-media
docker exec cityresq-minio mc policy set download local/cityresq-media
```

---

## 🔧 QUẢN LÝ SERVICES

### View Logs

```bash
# Development
docker-compose logs -f coreapi

# Production
docker-compose -f docker-compose.production.yml logs -f coreapi
```

### Resource Monitoring

```bash
# Container stats
docker stats

# Disk usage
docker system df

# Specific container
docker stats cityresq-coreapi
```

### Update Services

```bash
# Pull new code
git pull origin main

# Rebuild specific service
docker-compose build coreapi

# Restart without downtime
docker-compose up -d --no-deps --build coreapi
```

### Backup Databases

```bash
# MySQL
docker exec cityresq-mysql mysqldump -u root -p<password> cityresq_db > backup_$(date +%Y%m%d).sql

# MongoDB
docker exec cityresq-mongodb mongodump --username=cityresq --password=<password> --out=/tmp/backup
docker cp cityresq-mongodb:/tmp/backup ./backup_$(date +%Y%m%d)

# PostgreSQL
docker exec cityresq-postgres pg_dump -U cityresq wallet_db > backup_$(date +%Y%m%d).sql
```

---

## 🔧 TROUBLESHOOTING

### Container Won't Start

```bash
# Check logs
docker-compose logs <service-name>

# Check port conflict
sudo lsof -i :<port>

# Remove and recreate
docker-compose rm -f <service-name>
docker-compose up -d <service-name>
```

### Database Connection Failed

```bash
# Check database health
docker exec cityresq-mysql mysqladmin ping -h localhost

# Check environment variables
docker exec cityresq-coreapi env | grep DB_

# Restart database
docker-compose restart mysql
```

### Out of Disk Space

```bash
# Clean up
docker system prune -a
docker volume prune

# Remove unused images
docker rmi $(docker images -f "dangling=true" -q)
```

### Performance Issues

Check resource allocation:
```bash
docker stats
```

Add resource limits in docker-compose.yml:
```yaml
services:
  coreapi:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
```

### Reset Everything (CAUTION!)

```bash
# Stop and remove all
docker-compose down -v

# Remove images
docker rmi $(docker images -q)

# Start fresh
docker-compose up -d --build
```

---

## 📝 QUICK REFERENCE

### Development Commands

```bash
# Start
cd infrastructure/docker
docker-compose up -d

# Logs
docker-compose logs -f coreapi

# Shell access
docker exec -it cityresq-coreapi bash

# Artisan commands
docker exec cityresq-coreapi php artisan migrate

# Stop
docker-compose down
```

### Production Commands

```bash
# Start
docker-compose -f docker-compose.production.yml up -d

# Update
git pull && docker-compose -f docker-compose.production.yml up -d --build

# Logs
docker-compose -f docker-compose.production.yml logs -f

# Monitor
docker stats

# Stop
docker-compose -f docker-compose.production.yml down
```

---

## 🔐 DEFAULT CREDENTIALS

### Development

**Admin Panel:**
- URL: http://localhost:8000/admin
- Email: `admin@master.com`
- Password: `123456`

**RabbitMQ:**
- URL: http://localhost:15672
- User: `cityresq` / `cityresq_password`

**MinIO:**
- URL: http://localhost:9001
- User: `minioadmin` / `minioadmin`

### Production

⚠️ **Change all default passwords in .env file!**

---

## 📞 SUPPORT

Nếu gặp vấn đề:
1. Check logs: `docker-compose logs <service>`
2. Check status: `docker-compose ps`
3. Check troubleshooting section above
4. Check GitHub Issues

---

**Last Updated:** 2025-11-27  
**Docker Version:** 24.0+  
**Docker Compose Version:** 2.23+
