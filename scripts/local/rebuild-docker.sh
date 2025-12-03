#!/bin/bash
# ============================================
# CityResQ360 - Docker Clean & Rebuild Script
# ============================================
# Usage: ./rebuild-docker.sh
# ============================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

PROJECT_ROOT="/Volumes/MyVolume/Laravel/CityResQ360-DTUDZ"
COMPOSE_FILE="infrastructure/docker/docker-compose.yml"

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}CityResQ360 - Docker Rebuild${NC}"
echo -e "${CYAN}========================================${NC}"

# Change to project root
cd "$PROJECT_ROOT"

# Step 1: Stop all containers
echo -e "\n${YELLOW}[1/7] Stopping all containers...${NC}"
docker-compose -f "$COMPOSE_FILE" down || true

# Step 2: Remove volumes
echo -e "\n${YELLOW}[2/7] Removing volumes...${NC}"
docker volume ls | grep cityresq | awk '{print $2}' | xargs -r docker volume rm || true
echo -e "${GREEN}✓ Volumes removed${NC}"

# Step 3: Remove images
echo -e "\n${YELLOW}[3/7] Removing images...${NC}"
docker images | grep cityresq | awk '{print $3}' | xargs -r docker rmi -f || true
echo -e "${GREEN}✓ Images removed${NC}"

# Step 4: Prune system
echo -e "\n${YELLOW}[4/7] Pruning Docker system...${NC}"
docker system prune -f
echo -e "${GREEN}✓ System pruned${NC}"

# Step 5: Build images
echo -e "\n${YELLOW}[5/7] Building images (this may take 5-10 minutes)...${NC}"
docker-compose -f "$COMPOSE_FILE" build --no-cache
echo -e "${GREEN}✓ Images built${NC}"

# Step 6: Start databases first
echo -e "\n${YELLOW}[6/7] Starting database services...${NC}"
docker-compose -f "$COMPOSE_FILE" up -d postgres mysql redis mongodb rabbitmq minio
echo -e "${CYAN}Waiting 30 seconds for databases to initialize...${NC}"
sleep 30
echo -e "${GREEN}✓ Databases started${NC}"

# Step 7: Start application services
echo -e "\n${YELLOW}[7/7] Starting application services...${NC}"
docker-compose -f "$COMPOSE_FILE" up -d coreapi media-service notification-service wallet-service \
    iot-service floodeye-service incident-service analytics-service search-service
echo -e "${GREEN}✓ Application services started${NC}"

# Wait a bit for services to start
echo -e "\n${CYAN}Waiting 10 seconds for services to initialize...${NC}"
sleep 10

# Show status
echo -e "\n${CYAN}========================================${NC}"
echo -e "${CYAN}Container Status:${NC}"
echo -e "${CYAN}========================================${NC}"
docker-compose -f "$COMPOSE_FILE" ps

# Run migrations
echo -e "\n${YELLOW}Running database migrations...${NC}"
docker exec cityresq-coreapi php artisan migrate --force || true
docker exec cityresq-coreapi php artisan key:generate --force || true
docker exec cityresq-coreapi php artisan config:cache || true
echo -e "${GREEN}✓ Migrations complete${NC}"

# Test endpoints
echo -e "\n${CYAN}========================================${NC}"
echo -e "${CYAN}Testing Endpoints:${NC}"
echo -e "${CYAN}========================================${NC}"

echo -e "\n${YELLOW}CoreAPI:${NC}"
curl -s http://localhost:8000/api/health | head -n 5 || echo -e "${RED}✗ CoreAPI not responding${NC}"

echo -e "\n${YELLOW}MediaService:${NC}"
curl -s http://localhost:8001/health | head -n 5 || echo -e "${RED}✗ MediaService not responding${NC}"

echo -e "\n${YELLOW}FloodEyeService:${NC}"
curl -s http://localhost:8003/health | head -n 5 || echo -e "${RED}✗ FloodEyeService not responding${NC}"

echo -e "\n${YELLOW}IoTService:${NC}"
curl -s http://localhost:8004/health | head -n 5 || echo -e "${RED}✗ IoTService not responding${NC}"

echo -e "\n${YELLOW}IncidentService:${NC}"
curl -s http://localhost:8005/health | head -n 5 || echo -e "${RED}✗ IncidentService not responding${NC}"

echo -e "\n${YELLOW}AnalyticsService:${NC}"
curl -s http://localhost:8006/health | head -n 5 || echo -e "${RED}✗ AnalyticsService not responding${NC}"

echo -e "\n${YELLOW}SearchService:${NC}"
curl -s http://localhost:8007/health | head -n 5 || echo -e "${RED}✗ SearchService not responding${NC}"

# Instructions
echo -e "\n${CYAN}========================================${NC}"
echo -e "${GREEN}✓ Docker rebuild complete!${NC}"
echo -e "${CYAN}========================================${NC}"
echo -e "\n${CYAN}Access points:${NC}"
echo -e "  - CoreAPI:     ${GREEN}http://localhost:8000${NC}"
echo -e "  - Admin Panel: ${GREEN}http://localhost:8000/admin${NC}"
echo -e "  - MinIO UI:    ${GREEN}http://localhost:9001${NC}"
echo -e "  - RabbitMQ UI: ${GREEN}http://localhost:15672${NC}"
echo -e "\n${CYAN}View logs:${NC}"
echo -e "  docker-compose -f $COMPOSE_FILE logs -f"
echo -e "\n${CYAN}Stop all:${NC}"
echo -e "  docker-compose -f $COMPOSE_FILE down"
echo -e "${CYAN}========================================${NC}\n"
