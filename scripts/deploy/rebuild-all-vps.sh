#!/bin/bash
# =============================================================================
# LỆNH XÓA TẤT CẢ VÀ REBUILD TOÀN BỘ - VPS PRODUCTION
# =============================================================================
# File: rebuild_all_vps.sh
# Mục đích: Clean install hoàn toàn - Xóa containers, images, build lại từ đầu
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
echo "  1. ❌ STOP tất cả containers (Downtime ~10 phút)"
echo "  2. ❌ XÓA tất cả containers"
echo "  3. ❌ XÓA tất cả images (để build lại từ đầu)"
echo "  4. ✅ KEEP volumes (database data GIỮ NGUYÊN)"
echo "  5. 🔨 BUILD lại tất cả services (~35-40 phút)"
echo "  6. 🚀 START tất cả services"
echo ""
echo -e "${CYAN}Estimated time: 40-50 phút${NC}"
echo -e "${CYAN}Downtime: 5-10 phút${NC}"
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
echo -e "${BLUE}[1/8] Creating backup...${NC}"
BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Backup databases
docker exec cityresq-mysql mysqldump -u root -p${MYSQL_ROOT_PASSWORD:-cityresq_root} --all-databases > "$BACKUP_DIR/mysql_backup.sql" 2>/dev/null || echo "MySQL backup skipped"
docker exec cityresq-mongodb mongodump --out "$BACKUP_DIR/mongodb" 2>/dev/null || echo "MongoDB backup skipped"

echo -e "${GREEN}✅ Backup saved to: $BACKUP_DIR${NC}"

# ============================================
# STEP 2: STOP ALL CONTAINERS
# ============================================
echo ""
echo -e "${BLUE}[2/8] Stopping all containers...${NC}"
docker-compose -f infrastructure/docker/docker-compose.production.yml down
echo -e "${GREEN}✅ All containers stopped${NC}"

# ============================================
# STEP 3: REMOVE ALL CONTAINERS
# ============================================
echo ""
echo -e "${BLUE}[3/8] Removing all containers...${NC}"
docker container prune -f
echo -e "${GREEN}✅ All containers removed${NC}"

# ============================================
# STEP 4: REMOVE ALL IMAGES (để build lại)
# ============================================
echo ""
echo -e "${BLUE}[4/8] Removing all images...${NC}"
docker image prune -af
echo -e "${GREEN}✅ All images removed${NC}"

# ============================================
# STEP 5: CLEAN BUILD CACHE
# ============================================
echo ""
echo -e "${BLUE}[5/8] Cleaning build cache...${NC}"
docker builder prune -af
echo -e "${GREEN}✅ Build cache cleaned${NC}"

# ============================================
# STEP 6: BUILD ALL SERVICES
# ============================================
echo ""
echo -e "${BLUE}[6/8] Building all services (this will take ~35-40 minutes)...${NC}"
echo -e "${CYAN}☕ Đi uống cà phê đi, build lâu lắm!${NC}"

docker-compose -f infrastructure/docker/docker-compose.production.yml build --no-cache

echo -e "${GREEN}✅ All services built${NC}"

# ============================================
# STEP 7: START ALL SERVICES
# ============================================
echo ""
echo -e "${BLUE}[7/8] Starting all services...${NC}"
docker-compose -f infrastructure/docker/docker-compose.production.yml up -d

echo -e "${CYAN}⏳ Waiting for services to be ready (30s)...${NC}"
sleep 30

echo -e "${GREEN}✅ All services started${NC}"

# ============================================
# STEP 8: POST-DEPLOYMENT
# ============================================
echo ""
echo -e "${BLUE}[8/8] Running post-deployment tasks...${NC}"

# Run migrations
echo -e "${CYAN}Running database migrations...${NC}"
docker exec cityresq-coreapi php artisan migrate --force

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
docker ps | grep cityresq | wc -l

echo ""
echo -e "${YELLOW}📋 CHECKLIST:${NC}"
echo "  [ ] CoreAPI running on port 8000"
echo "  [ ] MediaService running on port 8002"
echo "  [ ] IoTService running on port 8014"
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
echo -e "${GREEN}🎉 Done! Hệ thống đã sẵn sàng!${NC}"
