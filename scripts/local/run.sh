#!/bin/bash
# ============================================
# CityResQ360 - Local Docker Management
# ============================================
# Quick commands for local development
# ============================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
COMPOSE_FILE="infrastructure/docker/docker-compose.yml"

cd "$PROJECT_ROOT"

# Show menu
show_menu() {
    echo -e "${CYAN}========================================${NC}"
    echo -e "${CYAN}CityResQ360 - Local Docker Manager${NC}"
    echo -e "${CYAN}========================================${NC}"
    echo -e "\n${YELLOW}Choose an option:${NC}"
    echo "  1) Start all services"
    echo "  2) Stop all services"
    echo "  3) Restart all services"
    echo "  4) Clean rebuild (remove everything)"
    echo "  5) View logs"
    echo "  6) Check status"
    echo "  7) Run migrations"
    echo "  8) Test endpoints"
    echo "  9) Open shells"
    echo "  0) Exit"
    echo -e "${CYAN}========================================${NC}"
}

# Start services
start_services() {
    echo -e "\n${YELLOW}Starting services...${NC}"
    
    # Start databases first
    echo -e "${CYAN}Starting databases...${NC}"
    docker-compose -f "$COMPOSE_FILE" up -d postgres mysql redis mongodb rabbitmq minio
    echo -e "${CYAN}Waiting 20 seconds for databases...${NC}"
    sleep 20
    
    # Start applications
    echo -e "${CYAN}Starting applications...${NC}"
    docker-compose -f "$COMPOSE_FILE" up -d coreapi media-service \
        iot-service floodeye-service incident-service analytics-service search-service aiml-service
    
    echo -e "${GREEN}✓ Services started${NC}"
    docker-compose -f "$COMPOSE_FILE" ps
}

# Stop services
stop_services() {
    echo -e "\n${YELLOW}Stopping services...${NC}"
    docker-compose -f "$COMPOSE_FILE" down
    echo -e "${GREEN}✓ Services stopped${NC}"
}

# Restart services
restart_services() {
    echo -e "\n${YELLOW}Restarting services...${NC}"
    docker-compose -f "$COMPOSE_FILE" restart
    echo -e "${GREEN}✓ Services restarted${NC}"
}

# Clean rebuild
clean_rebuild() {
    echo -e "\n${RED}⚠️  This will DELETE all data!${NC}"
    read -p "Are you sure? (yes/no): " confirm
    
    if [ "$confirm" != "yes" ]; then
        echo "Cancelled"
        return
    fi
    
    echo -e "\n${YELLOW}[1/7] Stopping containers...${NC}"
    docker-compose -f "$COMPOSE_FILE" down || true
    
    echo -e "\n${YELLOW}[2/7] Removing volumes...${NC}"
    docker volume ls | grep cityresq | awk '{print $2}' | xargs -r docker volume rm || true
    
    echo -e "\n${YELLOW}[3/7] Removing images...${NC}"
    docker images | grep cityresq | awk '{print $3}' | xargs -r docker rmi -f || true
    
    echo -e "\n${YELLOW}[4/7] Pruning system...${NC}"
    docker system prune -f
    
    echo -e "\n${YELLOW}[5/7] Building images...${NC}"
    docker-compose -f "$COMPOSE_FILE" build --no-cache
    
    echo -e "\n${YELLOW}[6/7] Starting databases...${NC}"
    docker-compose -f "$COMPOSE_FILE" up -d postgres mysql redis mongodb rabbitmq minio
    sleep 30
    
    echo -e "\n${YELLOW}[7/7] Starting applications...${NC}"
    docker-compose -f "$COMPOSE_FILE" up -d coreapi media-service \
        iot-service floodeye-service incident-service analytics-service search-service aiml-service
    sleep 10
    
    echo -e "\n${GREEN}✓ Clean rebuild complete${NC}"
    
    # Run migrations
    run_migrations
}

# View logs
view_logs() {
    echo -e "\n${CYAN}Select service:${NC}"
    echo "  1) All services"
    echo "  2) CoreAPI"
    echo "  3) MediaService"
    echo "  4) AIMLService"
    echo "  5) IoTService"
    echo "  6) FloodEyeService"
    echo "  7) IncidentService"
    echo "  8) AnalyticsService"
    echo "  9) SearchService"
    echo " 10) PostgreSQL"
    echo " 11) MySQL"
    read -p "Choice: " choice
    
    case $choice in
    case $choice in
        1) docker-compose -f "$COMPOSE_FILE" logs -f ;;
        2) docker-compose -f "$COMPOSE_FILE" logs -f coreapi ;;
        3) docker-compose -f "$COMPOSE_FILE" logs -f media-service ;;
        4) docker-compose -f "$COMPOSE_FILE" logs -f aiml-service ;;
        5) docker-compose -f "$COMPOSE_FILE" logs -f iot-service ;;
        6) docker-compose -f "$COMPOSE_FILE" logs -f floodeye-service ;;
        7) docker-compose -f "$COMPOSE_FILE" logs -f incident-service ;;
        8) docker-compose -f "$COMPOSE_FILE" logs -f analytics-service ;;
        9) docker-compose -f "$COMPOSE_FILE" logs -f search-service ;;
        10) docker-compose -f "$COMPOSE_FILE" logs -f postgres ;;
        11) docker-compose -f "$COMPOSE_FILE" logs -f mysql ;;
        *) echo "Invalid choice" ;;
    esac

# Check status
check_status() {
    echo -e "\n${CYAN}Container Status:${NC}"
    docker-compose -f "$COMPOSE_FILE" ps
    
    echo -e "\n${CYAN}Resource Usage:${NC}"
    docker stats --no-stream
}

# Run migrations
run_migrations() {
    echo -e "\n${YELLOW}Running migrations...${NC}"
    docker exec cityresq-coreapi php artisan migrate --force || true
    docker exec cityresq-coreapi php artisan key:generate --force || true
    docker exec cityresq-coreapi php artisan config:cache || true
    echo -e "${GREEN}✓ Migrations complete${NC}"
}

# Test endpoints
test_endpoints() {
    echo -e "\n${CYAN}Testing endpoints...${NC}"
    
    echo -e "\n${YELLOW}CoreAPI:${NC}"
    curl -s http://localhost:8000/api/health || echo -e "${RED}✗ Failed${NC}"
    
    echo -e "\n${YELLOW}MediaService:${NC}"
    echo -e "\n${YELLOW}MediaService:${NC}"
    curl -s http://localhost:8001/health || echo -e "${RED}✗ Failed${NC}"
    
    echo -e "\n${YELLOW}AIMLService:${NC}"
    curl -s http://localhost:8008/health || echo -e "${RED}✗ Failed${NC}"
    echo -e "\n${YELLOW}FloodEyeService:${NC}"
    curl -s http://localhost:8003/health || echo -e "${RED}✗ Failed${NC}"
    
    echo -e "\n${YELLOW}IoTService:${NC}"
    curl -s http://localhost:8004/health || echo -e "${RED}✗ Failed${NC}"
    
    echo -e "\n${YELLOW}IncidentService:${NC}"
    curl -s http://localhost:8005/health || echo -e "${RED}✗ Failed${NC}"
    
    echo -e "\n${YELLOW}AnalyticsService:${NC}"
    curl -s http://localhost:8006/health || echo -e "${RED}✗ Failed${NC}"
    
    echo -e "\n${YELLOW}SearchService:${NC}"
    curl -s http://localhost:8007/health || echo -e "${RED}✗ Failed${NC}"
    
    echo -e "\n${CYAN}URLs:${NC}"
    echo -e "  CoreAPI:         ${GREEN}http://localhost:8000${NC}"
    echo -e "  Admin:           ${GREEN}http://localhost:8000/admin${NC}"
    echo -e "  MediaService:    ${GREEN}http://localhost:8001${NC}"
    echo -e "  FloodEyeService: ${GREEN}http://localhost:8003${NC}"
    echo -e "  IoTService:      ${GREEN}http://localhost:8004${NC}"
    echo -e "  IncidentService: ${GREEN}http://localhost:8005${NC}"
    echo -e "  AnalyticsService:${GREEN}http://localhost:8006${NC}"
    echo -e "  SearchService:   ${GREEN}http://localhost:8007${NC}"
    echo -e "  MinIO:           ${GREEN}http://localhost:9001${NC}"
    echo -e "  RabbitMQ:        ${GREEN}http://localhost:15672${NC}"
}

# Open shells
open_shells() {
    echo -e "\n${CYAN}Select container:${NC}"
    echo "  1) CoreAPI (bash)"
    echo "  2) MediaService (sh)"
    echo "  3) PostgreSQL (psql)"
    echo "  4) MySQL (mysql)"
    echo "  5) Redis (redis-cli)"
    read -p "Choice: " choice
    
    case $choice in
        1) docker exec -it cityresq-coreapi bash ;;
        2) docker exec -it cityresq-media-service sh ;;
        3) docker exec -it cityresq-postgres psql -U cityresq -d cityresq_db ;;
        4) docker exec -it cityresq-mysql mysql -u cityresq -pcityresq_password cityresq ;;
        5) docker exec -it cityresq-redis redis-cli ;;
        *) echo "Invalid choice" ;;
    esac
}

# Main loop
while true; do
    show_menu
    read -p "Choose option: " option
    
    case $option in
        1) start_services ;;
        2) stop_services ;;
        3) restart_services ;;
        4) clean_rebuild ;;
        5) view_logs ;;
        6) check_status ;;
        7) run_migrations ;;
        8) test_endpoints ;;
        9) open_shells ;;
        0) echo -e "${GREEN}Goodbye!${NC}"; exit 0 ;;
        *) echo -e "${RED}Invalid option${NC}" ;;
    esac
    
    echo -e "\n${CYAN}Press Enter to continue...${NC}"
    read
done
