# 🐳 Docker Setup Guide - CityResQ360

## Quick Start

### Development Environment

```bash
# Navigate to docker directory
cd infrastructure/docker

# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop all services
docker-compose down
```

### Production Environment

```bash
# Navigate to docker directory
cd infrastructure/docker

# Start production services
docker-compose -f docker-compose.production.yml up -d

# View logs
docker-compose -f docker-compose.production.yml logs -f

# Stop all services
docker-compose -f docker-compose.production.yml down
```

## Services Overview

| Service | Port | Description |
|---------|------|-------------|
| **CoreAPI** | 8000 | Laravel 12 - Main API |
| **AppMobile** | 3000 | Next.js - Mobile App |
| **IncidentService** | 8001 | Incident Management |
| **IoTService** | 8002 | IoT Sensors Management |
| **AIMLService** | 8003 | AI/ML Processing |
| **MediaService** | 8004 | Media Storage |
| **WalletService** | 8005 | CityPoint Wallet |
| **NotificationService** | 8006 | Push Notifications |
| **SearchService** | 8007 | Search Engine |
| **FloodEyeService** | 8008 | Flood Monitoring |
| **AnalyticsService** | 8009 | Analytics Dashboard |
| **ContextBroker** | 1026 | NGSI-LD Broker |

### Infrastructure Services

| Service | Port | Management UI |
|---------|------|---------------|
| **MySQL** | 3307 | - |
| **PostgreSQL** | 5432 | - |
| **MongoDB** | 27017 | - |
| **Redis** | 6379 | - |
| **RabbitMQ** | 5672 | http://localhost:15672 (cityresq/cityresq_password) |
| **MinIO** | 9000 | http://localhost:9001 (minioadmin/minioadmin) |
| **OpenSearch** | 9200 | http://localhost:5601 |
| **ClickHouse** | 8123 | - |
| **MQTT** | 1883 | - |

## Development Tips

### Rebuild a specific service

```bash
cd infrastructure/docker
docker-compose build coreapi
docker-compose up -d coreapi
```

### Access service logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f coreapi

# Last 100 lines
docker-compose logs --tail=100 coreapi
```

### Execute commands in a container

```bash
# Laravel commands
docker-compose exec coreapi php artisan migrate

# Node.js commands
docker-compose exec media-service npm install

# Shell access
docker-compose exec coreapi bash
```

### Reset databases

```bash
# Stop services
docker-compose down

# Remove volumes (⚠️ This will delete all data)
docker-compose down -v

# Start fresh
docker-compose up -d
```

## Troubleshooting

### Port already in use

```bash
# Find process using port 8000
lsof -i :8000

# Kill process
kill -9 <PID>
```

### Service won't start

```bash
# Check service status
docker-compose ps

# Check logs for errors
docker-compose logs coreapi

# Rebuild without cache
docker-compose build --no-cache coreapi
docker-compose up -d coreapi
```

### Database connection errors

```bash
# Ensure database is healthy
docker-compose ps

# Check database logs
docker-compose logs mysql

# Restart database
docker-compose restart mysql
```

## Advanced Configuration

### Environment Variables

Copy `.env.example` in each service directory and configure:

```bash
# CoreAPI
cp modules/CoreAPI/.env.example modules/CoreAPI/.env

# MediaService
cp modules/MediaService/.env.example modules/MediaService/.env
```

### Custom Docker Compose

Create `docker-compose.override.yml` for local customizations:

```yaml
version: '3.8'

services:
  coreapi:
    ports:
      - "8080:8000"  # Custom port mapping
```

## Health Checks

Check if all services are healthy:

```bash
docker-compose ps
```

Healthy services should show `Up (healthy)` status.

## Resource Management

### View resource usage

```bash
docker stats
```

### Clean up unused resources

```bash
# Remove stopped containers
docker container prune

# Remove unused images
docker image prune

# Remove unused volumes
docker volume prune

# Remove everything unused
docker system prune -a
```

## Production Deployment

See [deploy.sh](../../scripts/deploy/deploy.sh) for production deployment script.

For more detailed information, see:
- [PROJECT_CONTEXT.md](../docs/PROJECT_CONTEXT.md)
- [DEVELOPMENT_WORKFLOW.md](../docs/DEVELOPMENT_WORKFLOW.md)
