#!/bin/bash

# ============================================
# CityResQ360 Production Deployment Script v2.0
# Supports: Domain-based (with SSL subdomains) or IP-only
# Architecture: Microservices with dedicated subdomains
# ============================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}🚀 CityResQ360 Production Deployment v2.0${NC}"
echo -e "${BLUE}================================================${NC}"
echo ""

# Check root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Please run as root: sudo ./deploy.sh${NC}"
    exit 1
fi

# Configuration
PROJECT_DIR="/opt/CityResQ360"
DOCKER_DIR="${PROJECT_DIR}/infrastructure/docker"
BACKUP_DIR="/opt/backups/cityresq360"
COMPOSE_FILE="docker-compose.yml"

# ============================================
# STEP 1: FRESH DEPLOYMENT OPTION
# ============================================
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${CYAN}Deployment type:${NC}"
echo "1) 🔄 Update (keep existing data)"
echo "2) 🗑️  Fresh (delete all containers & databases)"
echo ""
read -p "Enter choice [1-2]: " FRESH_DEPLOY

if [ "$FRESH_DEPLOY" = "2" ]; then
    CLEAN_INSTALL=true
    echo -e "${RED}⚠️  WARNING: All data will be deleted!${NC}"
else
    CLEAN_INSTALL=false
    echo -e "${GREEN}✅ Existing data will be preserved${NC}"
fi
echo ""

# ============================================
# STEP 2: DEPLOYMENT MODE SELECTION
# ============================================
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${CYAN}Select deployment mode:${NC}"
echo "1) 🌐 Domain-based (with SSL for each microservice)"
echo "   Example: api.yoursite.com, media.yoursite.com"
echo "   Recommended for: Production, API ecosystem"
echo ""
echo "2) 🖥️  IP-only (direct access via IP:PORT)"
echo "   Example: 34.85.44.142:8000"
echo "   Recommended for: Testing, development"
echo ""
read -p "Enter choice [1-2]: " DEPLOY_MODE

if [ "$DEPLOY_MODE" = "1" ]; then
    USE_DOMAIN=true
    echo ""
    echo -e "${YELLOW}📝 Domain-based deployment selected${NC}"
    read -p "Enter your domain (e.g., cityresq360.com): " DOMAIN
    read -p "Enter your email for SSL certificates: " EMAIL
    
    if [ -z "$DOMAIN" ] || [ -z "$EMAIL" ]; then
        echo -e "${RED}❌ Domain and email are required!${NC}"
        exit 1
    fi
    
    echo -e "${GREEN}✅ Domain: $DOMAIN${NC}"
    echo -e "${GREEN}✅ Email: $EMAIL${NC}"
    
    # Subdomains for microservices
    API_URL="api.$DOMAIN"
    MEDIA_URL="media.$DOMAIN"
    NOTIFICATION_URL="notification.$DOMAIN"
    WALLET_URL="wallet.$DOMAIN"
    INCIDENT_URL="incident.$DOMAIN"
    IOT_URL="iot.$DOMAIN"
    AIML_URL="aiml.$DOMAIN"
    SEARCH_URL="search.$DOMAIN"
    FLOODEYE_URL="floodeye.$DOMAIN"
    ANALYTICS_URL="analytics.$DOMAIN"
else
    USE_DOMAIN=false
    SERVER_IP=$(curl -s ifconfig.me || echo "localhost")
    echo ""
    echo -e "${YELLOW}🖥️  IP-only deployment selected${NC}"
    echo -e "${GREEN}✅ Server IP: $SERVER_IP${NC}"
    
    # IP-based URLs
    API_URL="http://$SERVER_IP:8000"
    MEDIA_URL="http://$SERVER_IP:8004"
    NOTIFICATION_URL="http://$SERVER_IP:8006"
    WALLET_URL="http://$SERVER_IP:8005"
    INCIDENT_URL="http://$SERVER_IP:8001"
    IOT_URL="http://$SERVER_IP:8002"
    AIML_URL="http://$SERVER_IP:8003"
    SEARCH_URL="http://$SERVER_IP:8007"
    FLOODEYE_URL="http://$SERVER_IP:8008"
    ANALYTICS_URL="http://$SERVER_IP:8009"
fi

echo ""

# ============================================
# STEP 3: INSTALL DEPENDENCIES
# ============================================
echo -e "${YELLOW}[Step 1/8] System Update${NC}"
apt-get update -qq
apt-get upgrade -y -qq

echo -e "${YELLOW}[Step 2/8] Installing Docker${NC}"
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
    usermod -aG docker root
    echo -e "${GREEN}✅ Docker installed${NC}"
else
    echo -e "${GREEN}✅ Docker already installed${NC}"
fi

if ! command -v docker-compose &> /dev/null; then
    curl -sL "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    echo -e "${GREEN}✅ Docker Compose installed${NC}"
else
    echo -e "${GREEN}✅ Docker Compose already installed${NC}"
fi

# Install Nginx and Certbot (only for domain mode)
if [ "$USE_DOMAIN" = true ]; then
    echo -e "${YELLOW}[Step 3/8] Installing Nginx${NC}"
    if ! command -v nginx &> /dev/null; then
        apt-get install -y nginx
        systemctl enable nginx
        systemctl start nginx
        echo -e "${GREEN}✅ Nginx installed${NC}"
    else
        echo -e "${GREEN}✅ Nginx already installed${NC}"
    fi
    
    echo -e "${YELLOW}[Step 4/8] Installing Certbot${NC}"
    if ! command -v certbot &> /dev/null; then
        apt-get install -y certbot python3-certbot-nginx
        echo -e "${GREEN}✅ Certbot installed${NC}"
    else
        echo -e "${GREEN}✅ Certbot already installed${NC}"
    fi

    # Create Nginx config for subdomains
    echo -e "${YELLOW}[Step 4b/8] Configuring Nginx for subdomains...${NC}"
    cat > /etc/nginx/sites-available/cityresq360 << EOF
# CoreAPI & Admin
server {
    listen 80;
    server_name api.$DOMAIN;
    
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}

# MediaService (proxies to MinIO for serving media files)
server {
    listen 80;
    server_name media.$DOMAIN;
    
    client_max_body_size 100M;
    
    location / {
        proxy_pass http://localhost:9000;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        
        # CORS headers for media files
        add_header Access-Control-Allow-Origin * always;
        add_header Access-Control-Allow-Methods 'GET, OPTIONS' always;
    }
}

# NotificationService
server {
    listen 80;
    server_name notification.$DOMAIN;
    
    location / {
        proxy_pass http://localhost:8006;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}

# WalletService
server {
    listen 80;
    server_name wallet.$DOMAIN;
    
    location / {
        proxy_pass http://localhost:8005;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

    # Enable site
    ln -sf /etc/nginx/sites-available/cityresq360 /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    
    # Test and reload
    nginx -t && systemctl reload nginx
    echo -e "${GREEN}✅ Nginx configured for $DOMAIN${NC}"
else
    echo -e "${YELLOW}[Step 3/8] Skipping Nginx (IP mode)${NC}"
    echo -e "${YELLOW}[Step 4/8] Skipping Certbot (IP mode)${NC}"
fi

# ============================================
# STEP 4: CLONE/UPDATE REPOSITORY
# ============================================
echo -e "${YELLOW}[Step 5/8] Repository Setup${NC}"

if [ -d "$PROJECT_DIR/.git" ]; then
    echo -e "${CYAN}Updating existing repository...${NC}"
    cd "$PROJECT_DIR"
    git stash || true
    git pull origin master
    echo -e "${GREEN}✅ Repository updated${NC}"
else
    echo -e "${CYAN}Cloning repository...${NC}"
    read -p "Enter Git repository URL: " REPO_URL
    
    if [ -z "$REPO_URL" ]; then
        echo -e "${RED}❌ Repository URL required!${NC}"
        exit 1
    fi
    
    git clone "$REPO_URL" "$PROJECT_DIR"
    cd "$PROJECT_DIR"
    echo -e "${GREEN}✅ Repository cloned${NC}"
fi

# ============================================
# STEP 5: CONFIGURE ENVIRONMENT
# ============================================
echo -e "${YELLOW}[Step 6/8] Environment Configuration${NC}"

ENV_FILE="${DOCKER_DIR}/.env"

# Generate passwords
if [ ! -f "$ENV_FILE" ]; then
    echo -e "${CYAN}Generating secure passwords...${NC}"
    
    MYSQL_PASS=$(openssl rand -hex 32)
    MONGO_PASS=$(openssl rand -hex 32)
    POSTGRES_PASS=$(openssl rand -hex 32)
    RABBITMQ_PASS=$(openssl rand -hex 32)
    MINIO_PASS=$(openssl rand -hex 32)
    JWT_SECRET=$(openssl rand -hex 64)
    APP_KEY="base64:$(openssl rand -base64 32)"
    
    # Determine MAIL_FROM_DOMAIN early for SMTP prompt
    if [ "$USE_DOMAIN" = true ]; then
        MAIL_FROM_DOMAIN="$DOMAIN"
    else
        MAIL_FROM_DOMAIN="cityresq360.com"
    fi

    # ============================================
    # SMTP CONFIGURATION (Interactive)
    # ============================================
    echo ""
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}📧 Email Configuration (SMTP)${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${YELLOW}Required for: User notifications, approval emails, password resets${NC}"
    echo ""
    read -p "Configure SMTP now? [Y/n]: " CONFIGURE_SMTP
    
    if [[ ! "$CONFIGURE_SMTP" =~ ^[Nn]$ ]]; then
        echo ""
        echo -e "${BLUE}Enter SMTP details:${NC}"
        
    # Prompt for SMTP configuration
read -p "Configure email settings? [Y/n]: " CONFIGURE_MAIL
if [[ ! "$CONFIGURE_MAIL" =~ ^[Nn]$ ]]; then
    echo -e "${CYAN}Configuring email settings...${NC}"
    
    read -p "MAIL_MAILER [smtp]: " MAIL_MAILER
    MAIL_MAILER=${MAIL_MAILER:-smtp}
    
    read -p "MAIL_HOST [smtp.gmail.com]: " MAIL_HOST
    MAIL_HOST=${MAIL_HOST:-smtp.gmail.com}
    
    read -p "MAIL_PORT [587]: " MAIL_PORT
    MAIL_PORT=${MAIL_PORT:-587}
    
    read -p "MAIL_USERNAME [thanhtruong23111999@gmail.com]: " MAIL_USERNAME
    MAIL_USERNAME=${MAIL_USERNAME:-thanhtruong23111999@gmail.com}
    
    read -p "MAIL_PASSWORD [ztiblxrmjkjqfxfc]: " MAIL_PASSWORD
    MAIL_PASSWORD=${MAIL_PASSWORD:-ztiblxrmjkjqfxfc}
    
    read -p "MAIL_FROM_ADDRESS [noreply@cityresq360.com]: " MAIL_FROM_ADDRESS
    MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-noreply@cityresq360.com}
    
    read -p "MAIL_FROM_NAME [CityResQ360]: " MAIL_FROM_NAME
    MAIL_FROM_NAME=${MAIL_FROM_NAME:-CityResQ360}
    
    # Append to .env
    cat >> modules/CoreAPI/.env <<EOF

# Mail Configuration
MAIL_MAILER=${MAIL_MAILER}
MAIL_SCHEME=null
MAIL_HOST=${MAIL_HOST}
MAIL_PORT=${MAIL_PORT}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS}"
MAIL_FROM_NAME="${MAIL_FROM_NAME}"
EOF
    
    echo -e "${GREEN}✅ Email configured successfully${NC}"
fi
        read -p "SMTP Username (email): " MAIL_USERNAME
        
        echo -e "${YELLOW}For Gmail, use App Password: https://myaccount.google.com/apppasswords${NC}"
        read -sp "SMTP Password: " MAIL_PASSWORD
        echo ""
        
        read -p "From Address [noreply@${MAIL_FROM_DOMAIN}]: " MAIL_FROM_INPUT
        MAIL_FROM_ADDRESS=${MAIL_FROM_INPUT:-noreply@${MAIL_FROM_DOMAIN}}
        
        echo ""
        echo -e "${GREEN}✅ SMTP configured${NC}"
    else
        echo -e "${YELLOW}⚠️  SMTP skipped - you can configure later in ${ENV_FILE}${NC}"
        MAIL_HOST="smtp.gmail.com"
        MAIL_PORT="587"
        MAIL_USERNAME=""
        MAIL_PASSWORD=""
        MAIL_FROM_ADDRESS="noreply@${MAIL_FROM_DOMAIN}"
    fi
    
    # Calculate URLs based on mode
    if [ "$USE_DOMAIN" = true ]; then
        FINAL_APP_URL="https://$API_URL"
        FINAL_API_URL="https://$API_URL/api/v1"
        FINAL_MEDIA_URL="https://$MEDIA_URL"
        MAIL_FROM_DOMAIN="$DOMAIN"
    else
        FINAL_APP_URL="$API_URL"
        FINAL_API_URL="$API_URL/api/v1"
        FINAL_MEDIA_URL="$MEDIA_URL"
        MAIL_FROM_DOMAIN="cityresq360.com"
    fi

    # Create .env
    cat > "$ENV_FILE" << EOF
# CityResQ360 Production Environment
# Generated: $(date)
# Deployment Mode: $([ "$USE_DOMAIN" = true ] && echo "Domain-based" || echo "IP-only")

# ============================================
# Laravel Application
# ============================================
APP_NAME=CityResQ360
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_TIMEZONE=Asia/Ho_Chi_Minh
APP_URL=${FINAL_APP_URL}
APP_LOCALE=vi

# ============================================
# Database Passwords
# ============================================
MYSQL_ROOT_PASSWORD=${MYSQL_PASS}
MYSQL_PASSWORD=${MYSQL_PASS}
MONGODB_PASSWORD=${MONGO_PASS}
POSTGRES_PASSWORD=${POSTGRES_PASS}
RABBITMQ_PASSWORD=${RABBITMQ_PASS}
CLICKHOUSE_PASSWORD=${MYSQL_PASS}

# ============================================
# MySQL (Laravel)
# ============================================
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cityresq_db
DB_USERNAME=cityresq
DB_PASSWORD=${MYSQL_PASS}

# ============================================
# Redis
# ============================================
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

# ============================================
# RabbitMQ
# ============================================
QUEUE_CONNECTION=rabbitmq
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=cityresq
RABBITMQ_PASSWORD=${RABBITMQ_PASS}

# ============================================
# MinIO (S3-compatible storage)
# ============================================
MINIO_ROOT_USER=admin
MINIO_ROOT_PASSWORD=${MINIO_PASS}
AWS_ACCESS_KEY_ID=admin
AWS_SECRET_ACCESS_KEY=${MINIO_PASS}
AWS_BUCKET=cityresq-media
AWS_ENDPOINT=http://minio:9000

# ============================================
# JWT
# ============================================
JWT_SECRET=${JWT_SECRET}
JWT_TTL=60

# ============================================
# Microservices URLs (Internal)
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
# Public URLs (for clients)
# ============================================
NEXT_PUBLIC_API_URL=${FINAL_API_URL}
NEXT_PUBLIC_MEDIA_URL=${FINAL_MEDIA_URL}

# ============================================
# SMTP Configuration
# ============================================
MAIL_HOST=${MAIL_HOST}
MAIL_PORT=${MAIL_PORT}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}

# ============================================
# FCM (Optional - configure if needed)
# ============================================
# FCM_PROJECT_ID=
# FCM_CLIENT_EMAIL=
# FCM_PRIVATE_KEY=
EOF

    chmod 600 "$ENV_FILE"
    echo -e "${GREEN}✅ Environment configured${NC}"
    echo -e "${YELLOW}📝 Passwords saved to: ${ENV_FILE}${NC}"
else
    echo -e "${YELLOW}⚠️  .env file exists, keeping current configuration${NC}"
fi

# ============================================
# STEP 6: DEPLOY WITH DOCKER
# ============================================
echo -e "${YELLOW}[Step 7/8] Docker Deployment${NC}"

cd "$DOCKER_DIR"

# Handle fresh deployment
if [ "$CLEAN_INSTALL" = true ]; then
    echo -e "${RED}🗑️  Performing fresh deployment...${NC}"
    
    # Stop all containers
    echo -e "${CYAN}[1/5] Stopping containers...${NC}"
    docker-compose -f "$COMPOSE_FILE" down 2>/dev/null || true
    
    # Remove all cityresq volumes
    echo -e "${CYAN}[2/5] Removing volumes...${NC}"
    docker volume ls | grep cityresq | awk '{print $2}' | xargs -r docker volume rm 2>/dev/null || true
    
    # Remove cityresq images
    echo -e "${CYAN}[3/5] Removing images...${NC}"
    docker images | grep cityresq | awk '{print $3}' | xargs -r docker rmi -f 2>/dev/null || true
    
    # Clean docker system
    echo -e "${CYAN}[4/5] Cleaning Docker system...${NC}"
    docker system prune -f --volumes
    
    echo -e "${GREEN}✅ Fresh deployment prepared${NC}"
else
    # Backup existing deployment
    if docker ps -q --filter name=cityresq- | grep -q .; then
        echo -e "${CYAN}Backing up current deployment...${NC}"
        mkdir -p "$BACKUP_DIR"
        BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S)"
        
        # Backup MySQL
        docker exec cityresq-mysql mysqldump -u root -p${MYSQL_PASSWORD} --all-databases > "${BACKUP_DIR}/${BACKUP_NAME}_mysql.sql" 2>/dev/null || true
        
        echo -e "${GREEN}✅ Backup created${NC}"
        
        # Stop containers
        echo -e "${CYAN}Stopping current containers...${NC}"
        docker-compose -f "$COMPOSE_FILE" down
    fi
fi

# Build and start
echo -e "${CYAN}Building services...${NC}"
docker-compose -f "$COMPOSE_FILE" build --no-cache

echo -e "${CYAN}Starting infrastructure...${NC}"
docker-compose -f "$COMPOSE_FILE" up -d mysql mongodb postgres redis rabbitmq minio

echo -e "${YELLOW}⏳ Waiting for databases (30s)...${NC}"
sleep 30

echo -e "${CYAN}Starting application services...${NC}"
docker-compose -f "$COMPOSE_FILE" up -d coreapi media-service notification-service iot-service incident-service analytics-service search-service aiml-service floodeye-service

echo -e "${GREEN}✅ Docker deployment complete!${NC}"

# Run migrations and optimize
echo -e "${CYAN}Running database migrations...${NC}"
sleep 10
docker exec cityresq-coreapi php artisan migrate --force || true
docker exec cityresq-coreapi php artisan db:seed --force || true

# Build frontend assets
echo -e "${CYAN}Building frontend assets (Vite)...${NC}"
if docker exec cityresq-coreapi which npm > /dev/null 2>&1; then
    echo -e "${YELLOW}Installing npm dependencies...${NC}"
    docker exec cityresq-coreapi sh -c "cd /var/www && npm install --no-audit --no-fund"
    echo -e "${YELLOW}Building production assets...${NC}"
    docker exec cityresq-coreapi sh -c "cd /var/www && npm run build"
    echo -e "${GREEN}✅ Frontend assets built successfully!${NC}"
else
    echo -e "${RED}⚠️  npm not found in container, skipping frontend build${NC}"
fi

# Ensure APP_KEY is generated and configuration is cached
echo -e "${CYAN}Optimizing Laravel configuration...${NC}"
docker exec cityresq-coreapi php artisan key:generate --force || true
docker exec cityresq-coreapi php artisan config:clear
docker exec cityresq-coreapi php artisan cache:clear
docker exec cityresq-coreapi php artisan config:cache
echo -e "${GREEN}✅ Laravel optimization complete!${NC}"

# Generate Swagger documentation
echo -e "${CYAN}Generating Swagger documentation...${NC}"
docker exec cityresq-coreapi php artisan l5-swagger:generate || echo "Swagger generation skipped"
docker exec cityresq-coreapi cp storage/api-docs/api-docs.json public/api-docs.json 2>/dev/null || echo "api-docs.json copy skipped"
echo -e "${GREEN}✅ Swagger docs generated${NC}"

# ============================================
# STEP 7: CONFIGURE SSL (Domain mode only)
# ============================================
if [ "$USE_DOMAIN" = true ]; then
    echo -e "${YELLOW}[Step 8/8] SSL Configuration${NC}"
    
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}DNS Configuration Required:${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo "$(curl -s ifconfig.me)"
    echo "A     api            → $(curl -s ifconfig.me)"
    echo "A     media          → $(curl -s ifconfig.me)"
    echo "A     notification   → $(curl -s ifconfig.me)"
   echo "A     wallet         → $(curl -s ifconfig.me)"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    
    read -p "Press Enter after DNS is configured..."
    
    # Generate SSL certificates
    echo -e "${CYAN}Generating SSL certificates...${NC}"
    
    certbot --nginx -d api.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect || true
    certbot --nginx -d media.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect || true
    
    # Auto-renew
    (crontab -l 2>/dev/null; echo "0 0 * * * certbot renew --quiet") | crontab -
    
    echo -e "${GREEN}✅ SSL certificates configured${NC}"
else
    echo -e "${YELLOW}[Step 8/8] Skipping SSL (IP mode)${NC}"
fi

# ============================================
# DEPLOYMENT SUMMARY
# ============================================
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🎉 Deployment Complete!${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${CYAN}📋 Deployment Configuration:${NC}"
echo -e "  Docker Compose: ${GREEN}docker-compose.yml${NC}"
echo -e "  Deployment Type: ${GREEN}$([ "$CLEAN_INSTALL" = true ] && echo "Fresh (all data cleared)" || echo "Update (data preserved)")${NC}"
echo -e "  Mode: ${GREEN}$([ "$USE_DOMAIN" = true ] && echo "Domain-based with SSL" || echo "IP-only")${NC}"
echo ""
echo -e "${GREEN}📊 Service URLs:${NC}"

if [ "$USE_DOMAIN" = true ]; then
    echo -e "  CoreAPI:       https://$API_URL"
    echo -e "  Admin Panel:   https://$API_URL/admin"
    echo -e "  MediaService:  https://$MEDIA_URL"
else
    echo -e "  CoreAPI:       $API_URL"
    echo -e "  Admin Panel:   $API_URL/admin"
    echo -e "  MediaService:  $MEDIA_URL"
    echo -e "  RabbitMQ:      http://$SERVER_IP:15672"
    echo -e "  MinIO:         http://$SERVER_IP:9001"
fi

echo ""
echo -e "${GREEN}📁 Important Paths:${NC}"
echo -e "  Project:       $PROJECT_DIR"
echo -e "  Docker:        $DOCKER_DIR"
echo -e "  Environment:   $ENV_FILE"
echo -e "  Backups:       $BACKUP_DIR"
echo ""
echo -e "${CYAN}🔧 Useful Commands:${NC}"
echo -e "  View logs:     docker-compose -f $DOCKER_DIR/docker-compose.yml logs -f"
echo -e "  Restart:       docker-compose -f $DOCKER_DIR/docker-compose.yml restart coreapi"
echo -e "  Stop all:      docker-compose -f $DOCKER_DIR/docker-compose.yml down"
echo ""
echo -e "${YELLOW}⚠️  Next Steps:${NC}"
echo -e "  1. Save passwords from: $ENV_FILE"

if [ "$USE_DOMAIN" = false ]; then
    echo -e "  2. Configure firewall: ufw allow 8000,8004,9001,15672/tcp"
fi

echo ""
echo -e "${GREEN}✅ CityResQ360 is now running!${NC}"
