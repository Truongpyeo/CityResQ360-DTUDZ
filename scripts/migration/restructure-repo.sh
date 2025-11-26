#!/bin/bash

# CityResQ360 Repository Restructuring Script
# This script automates the repository restructuring process

set -e  # Exit on error

echo "🚀 Starting CityResQ360 Repository Restructuring..."
echo "=================================================="

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "README.md" ] || [ ! -d "CoreAPI" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

echo -e "${BLUE}Step 1: Moving services to modules/${NC}"
# Move all services to modules/
git mv CoreAPI modules/CoreAPI
git mv AppMobile modules/AppMobile
git mv AIMLService modules/AIMLService
git mv AnalyticsService modules/AnalyticsService
git mv ContextBroker modules/ContextBroker
git mv FloodEyeService modules/FloodEyeService
git mv IncidentService modules/IncidentService
git mv IoTService modules/IoTService
git mv MediaService modules/MediaService
git mv NotificationService modules/NotificationService
git mv SearchService modules/SearchService
git mv WalletService modules/WalletService

echo -e "${GREEN}✅ Services moved to modules/${NC}"

echo -e "${BLUE}Step 2: Moving infrastructure files${NC}"
# Move Docker files
git mv docker-compose.yml infrastructure/docker/
git mv docker-compose.production.yml infrastructure/docker/

# Move nginx config
if [ -d "nginx" ]; then
    git mv nginx/* infrastructure/nginx/ 2>/dev/null || true
    rmdir nginx
fi

# Move mosquitto config
if [ -d "mosquitto" ]; then
    git mv mosquitto/* infrastructure/mosquitto/ 2>/dev/null || true
    rmdir mosquitto
fi

echo -e "${GREEN}✅ Infrastructure files moved${NC}"

echo -e "${BLUE}Step 3: Moving API collections${NC}"
# Move Postman collection
if [ -f "API_MNM_2025_1.postman_collection.json" ]; then
    git mv API_MNM_2025_1.postman_collection.json collections/postman/
fi

echo -e "${GREEN}✅ Collections moved${NC}"

echo -e "${BLUE}Step 4: Moving documentation${NC}"
# Move docs
git mv PROJECT_CONTEXT.md docs/
git mv DEVELOPMENT_WORKFLOW.md docs/
git mv DOCKER.md docs/

echo -e "${GREEN}✅ Documentation moved${NC}"

echo -e "${BLUE}Step 5: Moving scripts${NC}"
# Move deployment script
if [ -f "deploy.sh" ]; then
    git mv deploy.sh scripts/deploy/
fi

echo -e "${GREEN}✅ Scripts moved${NC}"

echo -e "${GREEN}=================================================="
echo -e "✅ Repository restructuring completed!"
echo -e "=================================================="
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Review the changes: git status"
echo "2. Update Docker Compose paths"
echo "3. Update CI/CD workflows"
echo "4. Update README.md"
echo "5. Commit changes: git commit -m 'refactor: restructure repository'"
