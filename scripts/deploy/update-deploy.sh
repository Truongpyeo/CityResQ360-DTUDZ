#!/bin/bash

# ============================================
# Smart Deploy Script
# Detects changes and rebuilds only affected services
# ============================================

set -e

# Configuration
PROJECT_DIR="/opt/CityResQ360" # Adjust if needed or use current dir
DOCKER_COMPOSE_FILE="infrastructure/docker/docker-compose.production.yml"
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

# ============================================
# STEP 2.5: ENVIRONMENT VARIABLES UPDATE (OPTIONAL)
# ============================================
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
read -p "Do you want to update environment variables? [y/N]: " UPDATE_ENV

if [[ "$UPDATE_ENV" =~ ^[Yy]$ ]]; then
    ENV_FILE="${PROJECT_DIR}/infrastructure/docker/.env"
    
    if [ ! -f "$ENV_FILE" ]; then
        echo -e "${RED}❌ .env file not found at: $ENV_FILE${NC}"
    else
        echo ""
        echo -e "${BLUE}Select service to configure:${NC}"
        echo "1) CoreAPI (SMTP, JWT, APP_KEY)"
        echo "2) MediaService (MinIO credentials)"
        echo "3) NotificationService (FCM, SMTP)"
        echo "4) Custom (manual key entry)"
        echo "5) Skip"
        echo ""
        read -p "Enter choice [1-5]: " SERVICE_CHOICE
        
        # Helper function to get current env value
        get_env_value() {
            local key=$1
            grep -E "^${key}=" "$ENV_FILE" 2>/dev/null | cut -d '=' -f2- | tr -d '"' || echo ""
        }
        
        # Helper function to update or append env value
        set_env_value() {
            local key=$1
            local value=$2
            
            if grep -q "^${key}=" "$ENV_FILE"; then
                # Update existing key (cross-platform sed)
                if [[ "$OSTYPE" == "darwin"* ]]; then
                    sed -i '' "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
                else
                    sed -i "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
                fi
                echo -e "${GREEN}✓ Updated ${key}${NC}"
            else
                # Append new key
                echo "${key}=${value}" >> "$ENV_FILE"
                echo -e "${GREEN}✓ Added ${key}${NC}"
            fi
        }
        
        # Process based on service choice
        case $SERVICE_CHOICE in
            1) # CoreAPI
                echo ""
                echo -e "${BLUE}━━━ CoreAPI Configuration ━━━${NC}"
                echo ""
                
                # SMTP Configuration
                echo -e "${YELLOW}SMTP Settings:${NC}"
                CURRENT_HOST=$(get_env_value "MAIL_HOST")
                read -p "SMTP Host [${CURRENT_HOST:-smtp.gmail.com}]: " NEW_HOST
                [ -n "$NEW_HOST" ] && set_env_value "MAIL_HOST" "$NEW_HOST" || true
                
                CURRENT_PORT=$(get_env_value "MAIL_PORT")
                read -p "SMTP Port [${CURRENT_PORT:-587}]: " NEW_PORT
                [ -n "$NEW_PORT" ] && set_env_value "MAIL_PORT" "$NEW_PORT" || true
                
                CURRENT_USER=$(get_env_value "MAIL_USERNAME")
                read -p "SMTP Username [${CURRENT_USER}]: " NEW_USER
                [ -n "$NEW_USER" ] && set_env_value "MAIL_USERNAME" "$NEW_USER" || true
                
                echo -e "${YELLOW}For Gmail, use App Password: https://myaccount.google.com/apppasswords${NC}"
                read -sp "SMTP Password (leave blank to keep current): " NEW_PASS
                echo ""
                [ -n "$NEW_PASS" ] && set_env_value "MAIL_PASSWORD" "$NEW_PASS" || true
                
                CURRENT_FROM=$(get_env_value "MAIL_FROM_ADDRESS")
                read -p "From Address [${CURRENT_FROM}]: " NEW_FROM
                [ -n "$NEW_FROM" ] && set_env_value "MAIL_FROM_ADDRESS" "$NEW_FROM" || true
                
                echo ""
                echo -e "${YELLOW}App Settings:${NC}"
                CURRENT_URL=$(get_env_value "APP_URL")
                read -p "APP_URL [${CURRENT_URL}]: " NEW_APP_URL
                [ -n "$NEW_APP_URL" ] && set_env_value "APP_URL" "$NEW_APP_URL" || true
                
                echo ""
                echo -e "${GREEN}✅ CoreAPI configuration updated${NC}"
                ;;
                
            2) # MediaService
                echo ""
                echo -e "${BLUE}━━━ MediaService Configuration ━━━${NC}"
                echo ""
                
                CURRENT_MINIO_USER=$(get_env_value "MINIO_ROOT_USER")
                read -p "MinIO User [${CURRENT_MINIO_USER:-admin}]: " NEW_MINIO_USER
                [ -n "$NEW_MINIO_USER" ] && set_env_value "MINIO_ROOT_USER" "$NEW_MINIO_USER" || true
                
                read -sp "MinIO Password (leave blank to keep current): " NEW_MINIO_PASS
                echo ""
                if [ -n "$NEW_MINIO_PASS" ]; then
                    set_env_value "MINIO_ROOT_PASSWORD" "$NEW_MINIO_PASS"
                    set_env_value "AWS_SECRET_ACCESS_KEY" "$NEW_MINIO_PASS"
                fi
                
                echo -e "${GREEN}✅ MediaService configuration updated${NC}"
                ;;
                
            3) # NotificationService
                echo ""
                echo -e "${BLUE}━━━ NotificationService Configuration ━━━${NC}"
                echo ""
                
                echo -e "${YELLOW}FCM Settings:${NC}"
                CURRENT_FCM_PROJECT=$(get_env_value "FCM_PROJECT_ID")
                read -p "FCM Project ID [${CURRENT_FCM_PROJECT}]: " NEW_FCM_PROJECT
                [ -n "$NEW_FCM_PROJECT" ] && set_env_value "FCM_PROJECT_ID" "$NEW_FCM_PROJECT" || true
                
                CURRENT_FCM_EMAIL=$(get_env_value "FCM_CLIENT_EMAIL")
                read -p "FCM Client Email [${CURRENT_FCM_EMAIL}]: " NEW_FCM_EMAIL
                [ -n "$NEW_FCM_EMAIL" ] && set_env_value "FCM_CLIENT_EMAIL" "$NEW_FCM_EMAIL" || true
                
                read -sp "FCM Private Key (leave blank to keep current): " NEW_FCM_KEY
                echo ""
                [ -n "$NEW_FCM_KEY" ] && set_env_value "FCM_PRIVATE_KEY" "$NEW_FCM_KEY" || true
                
                echo -e "${GREEN}✅ NotificationService configuration updated${NC}"
                ;;
                
            4) # Custom
                echo ""
                echo -e "${BLUE}━━━ Custom Configuration ━━━${NC}"
                echo -e "${YELLOW}Enter key-value pairs (empty key to finish)${NC}"
                echo ""
                
                while true; do
                    read -p "Key: " CUSTOM_KEY
                    [ -z "$CUSTOM_KEY" ] && break
                    
                    CURRENT_VAL=$(get_env_value "$CUSTOM_KEY")
                    if [ -n "$CURRENT_VAL" ]; then
                        echo "Current value: ${CURRENT_VAL}"
                    fi
                    
                    read -p "Value: " CUSTOM_VALUE
                    [ -n "$CUSTOM_VALUE" ] && set_env_value "$CUSTOM_KEY" "$CUSTOM_VALUE" || true
                done
                
                echo -e "${GREEN}✅ Custom configuration updated${NC}"
                ;;
                
            *)
                echo -e "${YELLOW}Skipping environment update${NC}"
                ;;
        esac
    fi
fi

# 3. Rebuild and Restart
echo -e "${YELLOW}[3/3] Rebuilding services: $SERVICES_TO_REBUILD${NC}"

# Build
docker compose -f $DOCKER_COMPOSE_FILE build $SERVICES_TO_REBUILD

# Up (Recreate)
docker compose -f $DOCKER_COMPOSE_FILE up -d $SERVICES_TO_REBUILD

# Post-deployment tasks (specific to CoreAPI)
if [[ "$SERVICES_TO_REBUILD" == *"coreapi"* ]]; then
    echo -e "${CYAN}Running CoreAPI post-deployment tasks...${NC}"
    
    # Always generate APP_KEY (ensures it exists and is valid)
    echo -e "${CYAN}Generating APP_KEY...${NC}"
    docker exec cityresq-coreapi php artisan key:generate --force
    
    # Clear all caches using optimize:clear
    echo -e "${CYAN}Clearing caches...${NC}"
    docker exec cityresq-coreapi php artisan optimize:clear
    
    # Restart container to ensure clean state
    echo -e "${CYAN}Restarting CoreAPI container...${NC}"
    docker restart cityresq-coreapi
    
    # Wait for container to be ready
    echo -e "${CYAN}Waiting for container to be ready...${NC}"
    sleep 10
fi

echo -e "${GREEN}✅ Smart deployment complete!${NC}"
