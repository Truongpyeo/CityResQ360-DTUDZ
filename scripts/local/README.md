# CityResQ360 - Local Development Scripts

## 🚀 Quick Start

### Interactive Menu (Recommended)
```bash
./scripts/local/run.sh
```

Sẽ hiển thị menu:
```
========================================
CityResQ360 - Local Docker Manager
========================================

Choose an option:
  1) Start all services
  2) Stop all services
  3) Restart all services
  4) Clean rebuild (remove everything)
  5) View logs
  6) Check status
  7) Run migrations
  8) Test endpoints
  9) Open shells
  0) Exit
========================================
```

---

## 📋 Common Tasks

### Start Services
```bash
# Option 1: Interactive
./scripts/local/run.sh
# Chọn: 1

# Option 2: Direct command
docker-compose -f infrastructure/docker/docker-compose.yml up -d
```

### Stop Services
```bash
# Option 1: Interactive
./scripts/local/run.sh
# Chọn: 2

# Option 2: Direct command
docker-compose -f infrastructure/docker/docker-compose.yml down
```

### Clean Rebuild (Xóa tất cả và build lại)
```bash
# Interactive
./scripts/local/run.sh
# Chọn: 4

# OR use rebuild script
./scripts/rebuild-docker.sh
```

### View Logs
```bash
# All services
docker-compose -f infrastructure/docker/docker-compose.yml logs -f

# Specific service
docker-compose -f infrastructure/docker/docker-compose.yml logs -f coreapi
docker-compose -f infrastructure/docker/docker-compose.yml logs -f media-service
```

### Run Migrations
```bash
docker exec cityresq-coreapi php artisan migrate
docker exec cityresq-coreapi php artisan db:seed
```

---

## 🔧 Troubleshooting

### Port Already in Use
```bash
# Find process using port
lsof -i :8000
lsof -i :5432

# Kill process
kill -9 <PID>
```

### Container Won't Start
```bash
# Check logs
docker logs cityresq-coreapi --tail 100

# Restart
docker restart cityresq-coreapi
```

### Database Connection Issues
```bash
# Restart database
docker-compose -f infrastructure/docker/docker-compose.yml restart postgres

# Check if ready
docker exec cityresq-postgres pg_isready
```

---

## 📊 URLs

| Service | URL |
|---------|-----|
| CoreAPI | http://localhost:8000 |
| Admin Panel | http://localhost:8000/admin |
| MediaService | http://localhost:8004 |
| NotificationService | http://localhost:8002 |
| WalletService | http://localhost:8003 |
| MinIO Console | http://localhost:9001 |
| RabbitMQ UI | http://localhost:15672 |

---

## 🗂️ Project Structure

```
CityResQ360-DTUDZ/
├── scripts/
│   ├── local/
│   │   └── run.sh          # Interactive management
│   ├── deploy/
│   │   └── deploy.sh       # Production deployment
│   └── rebuild-docker.sh   # Clean rebuild
├── infrastructure/
│   └── docker/
│       └── docker-compose.yml
└── modules/
    ├── CoreAPI/
    ├── MediaService/
    ├── NotificationService/
    └── WalletService/
```
