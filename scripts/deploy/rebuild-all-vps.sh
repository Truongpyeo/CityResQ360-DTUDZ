#!/bin/bash
# =============================================================================
# LỆNH XÓA TẤT CẢ VÀ REBUILD TOÀN BỘ - VPS PRODUCTION
# =============================================================================
# File: rebuild_all_vps.sh
# Mục đích: Clean install hoàn toàn - Xóa containers, images, volumes, build lại từ đầu
# QUAN TRỌNG: CHỈ XÓA VOLUMES CỦA CityResQ360, KHÔNG ẢNH HƯỞNG CÁC PROJECT KHÁC
# =============================================================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${RED}⚠️  CẢNH BÁO: SẼ XÓA TẤT CẢ VÀ REBUILD!${NC}"
echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}Thao tác này sẽ:${NC}"
echo "  1. ❌ STOP tất cả containers CityResQ360 (Downtime ~10 phút)"
echo "  2. ❌ XÓA tất cả containers CityResQ360"
echo "  3. ❌ XÓA tất cả images CityResQ360"
echo "  4. ❌ XÓA tất cả volumes CityResQ360 (DATABASE DATA SẼ MẤT!)"
echo "  5. 🔨 BUILD lại tất cả services (~35-40 phút)"
echo "  6. 🚀 START tất cả services"
echo ""
echo -e "${RED}⚠️  DATABASE DATA SẼ BỊ XÓA HOÀN TOÀN!${NC}"
echo -e "${CYAN}Chỉ xóa volumes của CityResQ360, không ảnh hưởng projects khác${NC}"
echo ""
echo -e "${CYAN}Estimated time: 40-50 phút${NC}"
echo -e "${CYAN}Downtime: 10 phút (toàn bộ hệ thống)${NC}"
echo ""

read -p "Bạn CHẮC CHẮN muốn tiếp tục? [y/N]: " CONFIRM
if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}❌ Hủy bỏ.${NC}"
    exit 0
fi

cd /opt/CityResQ360

# ============================================
# STEP 1: BACKUP (Optional but recommended)
# ============================================
echo ""
echo -e "${BLUE}[1/9] Creating backup (recommended)...${NC}"
read -p "Create database backup before deleting? [Y/n]: " DO_BACKUP

if [[ ! "$DO_BACKUP" =~ ^[Nn]$ ]]; then
    BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"
    
    # Backup databases
    echo -e "${CYAN}Backing up MySQL...${NC}"
    docker exec cityresq-mysql mysqldump -u root -p${MYSQL_ROOT_PASSWORD:-cityresq_root} --all-databases > "$BACKUP_DIR/mysql_backup.sql" 2>/dev/null || echo "MySQL backup skipped"
    
    echo -e "${CYAN}Backing up MongoDB...${NC}"
    docker exec cityresq-mongodb mongodump --out "$BACKUP_DIR/mongodb" 2>/dev/null || echo "MongoDB backup skipped"
    
    echo -e "${GREEN}✅ Backup saved to: $BACKUP_DIR${NC}"
else
    echo -e "${YELLOW}⚠️  Skipping backup${NC}"
fi

# ============================================
# STEP 2: STOP ALL CONTAINERS
# ============================================
echo ""
echo -e "${BLUE}[2/9] Stopping all CityResQ360 containers...${NC}"
docker-compose -f infrastructure/docker/docker-compose.production.yml down
echo -e "${GREEN}✅ All containers stopped${NC}"

# ============================================
# STEP 3: REMOVE CITYRESQ360 CONTAINERS ONLY
# ============================================
echo ""
echo -e "${BLUE}[3/9] Removing CityResQ360 containers only...${NC}"
docker ps -a --filter "name=cityresq" --format "{{.Names}}" | xargs -r docker rm -f
echo -e "${GREEN}✅ CityResQ360 containers removed${NC}"

# ============================================
# STEP 4: REMOVE CITYRESQ360 VOLUMES ONLY
# ============================================
echo ""
echo -e "${BLUE}[4/9] Removing CityResQ360 volumes only...${NC}"
echo -e "${YELLOW}⚠️  DATABASE DATA WILL BE DELETED!${NC}"
read -p "Confirm delete ALL CityResQ360 volumes? [y/N]: " CONFIRM_VOLUMES

if [[ "$CONFIRM_VOLUMES" =~ ^[Yy]$ ]]; then
    # List volumes to be deleted
    echo -e "${CYAN}Volumes to be deleted:${NC}"
    docker volume ls --filter "name=docker_" --format "{{.Name}}" | grep -E "docker_(mysql|mongodb|postgres|redis|minio|rabbitmq|clickhouse|timescaledb|opensearch|mosquitto|aiml|coreapi|grafana|prometheus)"
    
    # Delete CityResQ360 volumes
    docker volume ls --filter "name=docker_" --format "{{.Name}}" | grep -E "docker_(mysql|mongodb|postgres|redis|minio|rabbitmq|clickhouse|timescaledb|opensearch|mosquitto|aiml|coreapi|grafana|prometheus)" | xargs -r docker volume rm
    
    echo -e "${GREEN}✅ CityResQ360 volumes removed${NC}"
else
    echo -e "${YELLOW}⚠️  Keeping volumes (database data preserved)${NC}"
fi

# ============================================
# STEP 5: REMOVE CITYRESQ360 IMAGES ONLY
# ============================================
echo ""
echo -e "${BLUE}[5/9] Removing CityResQ360 images only...${NC}"
docker images --filter "reference=docker-*" --format "{{.Repository}}:{{.Tag}}" | xargs -r docker rmi -f
echo -e "${GREEN}✅ CityResQ360 images removed${NC}"

# ============================================
# STEP 6: CLEAN BUILD CACHE
# ============================================
echo ""
echo -e "${BLUE}[6/9] Cleaning build cache...${NC}"
docker builder prune -af
echo -e "${GREEN}✅ Build cache cleaned${NC}"

# ============================================
# STEP 7: BUILD ALL SERVICES
# ============================================
echo ""
echo -e "${BLUE}[7/9] Building all services (this will take ~35-40 minutes)...${NC}"
echo -e "${CYAN}☕ Đi uống cà phê đi, build lâu lắm!${NC}"
echo ""

# Show build progress
docker-compose -f infrastructure/docker/docker-compose.production.yml build --no-cache --progress=plain

echo -e "${GREEN}✅ All services built${NC}"

# ============================================
# STEP 8: START ALL SERVICES
# ============================================
echo ""
echo -e "${BLUE}[8/9] Starting all services...${NC}"
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d

echo -e "${CYAN}⏳ Waiting for services to be ready (30s)...${NC}"
sleep 30

echo -e "${GREEN}✅ All services started${NC}"

# ============================================
# STEP 9: POST-DEPLOYMENT
# ============================================
echo ""
echo -e "${BLUE}[9/9] Running post-deployment tasks...${NC}"

# Run migrations
echo -e "${CYAN}Running database migrations...${NC}"
docker exec cityresq-coreapi php artisan migrate --force

# Seed initial data (if needed)
echo -e "${CYAN}Seeding database...${NC}"
docker exec cityresq-coreapi php artisan db:seed --force

# Generate Swagger docs
echo -e "${CYAN}Generating Swagger documentation...${NC}"
docker exec cityresq-coreapi php artisan l5-swagger:generate
docker exec cityresq-coreapi cp storage/api-docs/api-docs.json public/api-docs.json 2>/dev/null || true

# Clear caches
echo -e "${CYAN}Clearing caches...${NC}"
docker exec cityresq-coreapi php artisan config:clear
docker exec cityresq-coreapi php artisan cache:clear
docker exec cityresq-coreapi php artisan config:cache

echo -e "${GREEN}✅ Post-deployment complete${NC}"

# ============================================
# VERIFICATION
# ============================================
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ REBUILD HOÀN TẤT!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${CYAN}Kiểm tra containers đang chạy:${NC}"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep cityresq

echo ""
echo -e "${CYAN}Services count:${NC}"
RUNNING_COUNT=$(docker ps | grep cityresq | wc -l)
echo "Running: $RUNNING_COUNT containers"

echo ""
echo -e "${YELLOW}📋 CHECKLIST:${NC}"
echo "  [ ] CoreAPI running on port 8000"
echo "  [ ] MediaService running on port 8002 (fixed from 8004)"
echo "  [ ] IoTService running on port 8014 (fixed from 8004)"
echo "  [ ] NotificationService on port 8006"
echo "  [ ] AnalyticsService on port 8009"
echo "  [ ] Context Broker Adapter on port 8010"
echo "  [ ] IoT Adapter on port 8011"
echo "  [ ] 3 Consumer services running (no ports)"
echo "  [ ] All databases healthy"
echo ""
echo -e "${CYAN}Test endpoints:${NC}"
echo "  curl http://localhost:8000/api/v1/health"
echo "  curl http://localhost:8000/api/documentation"
echo "  curl http://localhost:8000/ngsi-ld/v1/types"
echo ""
echo -e "${YELLOW}Port mapping summary:${NC}"
echo "  8000 → CoreAPI"
echo "  8002 → MediaService (internal: 8004)"
echo "  8014 → IoTService (internal: 8004)"
echo "  8006 → NotificationService"
echo "  8009 → AnalyticsService"
echo "  8010 → Context Broker Adapter"
echo "  8011 → IoT Adapter"
echo ""
echo -e "${GREEN}🎉 Done! Hệ thống đã sẵn sàng!${NC}"
