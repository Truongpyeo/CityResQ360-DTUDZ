#!/bin/bash

# ============================================
# Smart Deploy Script
# Detects changes and rebuilds only affected services
# ============================================

set -e

# Configuration
PROJECT_DIR="/opt/CityResQ360" # Adjust if needed or use current dir
DOCKER_COMPOSE_FILE="../../infrastructure/docker/docker-compose.yml"
BRANCH="master" # Default branch

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}🚀 Smart Deploy - CityResQ360${NC}"
echo -e "${BLUE}================================================${NC}"

# 1. Update Code
echo -e "${YELLOW}[1/3] Updating repository...${NC}"
git fetch origin $BRANCH
CHANGES=$(git diff --name-only HEAD origin/$BRANCH)

if [ -z "$CHANGES" ]; then
    echo -e "${GREEN}✅ No changes detected. System is up to date.${NC}"
    exit 0
fi

echo -e "${CYAN}Changes detected in:${NC}"
echo "$CHANGES" | head -n 5
if [ $(echo "$CHANGES" | wc -l) -gt 5 ]; then echo "...and more"; fi

git pull origin $BRANCH

# 2. Detect Services to Rebuild
echo -e "${YELLOW}[2/3] Analyzing changes...${NC}"
SERVICES_TO_REBUILD=""

# Map directories to services
declare -A SERVICE_MAP
SERVICE_MAP=(
    ["modules/CoreAPI"]="coreapi"
    ["modules/MediaService"]="media-service"
    ["modules/NotificationService"]="notification-service"
    ["modules/WalletService"]="wallet-service"
    ["modules/IncidentService"]="incident-service"
    ["modules/IoTService"]="iot-service"
    ["modules/AIMLService"]="aiml-service"
    ["modules/SearchService"]="search-service"
    ["modules/FloodEyeService"]="floodeye-service"
    ["modules/AnalyticsService"]="analytics-service"
)

# Check for infrastructure changes (rebuild all if infra changes)
if echo "$CHANGES" | grep -q "infrastructure/docker"; then
    echo -e "${RED}⚠️  Infrastructure changes detected. Recommending full rebuild.${NC}"
    read -p "Do you want to rebuild ALL services? [y/N] " REBUILD_ALL
    if [[ "$REBUILD_ALL" =~ ^[Yy]$ ]]; then
        SERVICES_TO_REBUILD="coreapi media-service notification-service wallet-service incident-service iot-service aiml-service search-service floodeye-service analytics-service"
    fi
fi

# Check module changes
if [ -z "$SERVICES_TO_REBUILD" ]; then
    for DIR in "${!SERVICE_MAP[@]}"; do
        if echo "$CHANGES" | grep -q "$DIR"; then
            SERVICE=${SERVICE_MAP[$DIR]}
            echo -e "${GREEN}👉 Detected changes in $DIR -> Rebuilding $SERVICE${NC}"
            SERVICES_TO_REBUILD="$SERVICES_TO_REBUILD $SERVICE"
        fi
    done
fi

if [ -z "$SERVICES_TO_REBUILD" ]; then
    echo -e "${GREEN}✅ No service-specific changes detected (only docs or scripts).${NC}"
    exit 0
fi

# 3. Rebuild and Restart
echo -e "${YELLOW}[3/3] Rebuilding services: $SERVICES_TO_REBUILD${NC}"

# Build
docker compose -f $DOCKER_COMPOSE_FILE build $SERVICES_TO_REBUILD

# Up (Recreate)
docker compose -f $DOCKER_COMPOSE_FILE up -d $SERVICES_TO_REBUILD

# Post-deployment tasks (specific to CoreAPI)
if [[ "$SERVICES_TO_REBUILD" == *"coreapi"* ]]; then
    echo -e "${CYAN}Clearing CoreAPI cache...${NC}"
    docker exec cityresq-coreapi php artisan optimize:clear
fi

echo -e "${GREEN}✅ Smart deployment complete!${NC}"
