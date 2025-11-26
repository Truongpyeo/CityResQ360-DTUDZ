#!/bin/bash

# Git Commit Script - Repository Restructuring
# This script commits all the restructuring changes

set -e

echo "🚀 Committing Repository Restructuring..."
echo "=================================================="

# Add all changes
echo "📦 Staging all changes..."
git add -A

# Create commit
echo "💾 Creating commit..."
git commit -m "refactor: restructure repository for better organization

BREAKING CHANGE: All services and infrastructure files have been reorganized

- Move all 12 microservices to modules/ directory
  - CoreAPI, AppMobile, AIMLService, AnalyticsService
  - ContextBroker, FloodEyeService, IncidentService
  - IoTService, MediaService, NotificationService
  - SearchService, WalletService

- Move infrastructure configs to infrastructure/
  - docker-compose.yml → infrastructure/docker/
  - docker-compose.production.yml → infrastructure/docker/
  - nginx/ → infrastructure/nginx/
  - mosquitto/ → infrastructure/mosquitto/

- Move documentation to docs/
  - PROJECT_CONTEXT.md → docs/
  - DEVELOPMENT_WORKFLOW.md → docs/
  - DOCKER.md → docs/

- Move API collections to collections/
  - Postman collection → collections/postman/

- Move utility scripts to scripts/
  - deploy.sh → scripts/deploy/
  - Created scripts/setup/ and scripts/migration/

- Update all Docker Compose paths (25+ updates)
  - All service contexts updated
  - All volume mounts updated
  - Mosquitto and nginx paths corrected

- Enhance .gitignore with module-specific patterns
- Update README with new project structure visualization
- Create comprehensive Docker guide in infrastructure/docker/README.md

Benefits:
- Root level now shows only 7 essential folders
- Easy to add new services in modules/
- Infrastructure configs grouped logically
- Follows open-source project best practices
- All file moves preserve git commit history

Migration Impact:
- Developers must pull and rebuild containers
- Docker commands must run from infrastructure/docker/
- All documentation paths updated"

echo ""
echo "✅ Commit created successfully!"
echo ""
echo "📤 Next steps:"
echo "  1. Push to repository: git push origin main"
echo "  2. Notify team about breaking changes"
echo "  3. Test Docker setup: cd infrastructure/docker && docker-compose up"
