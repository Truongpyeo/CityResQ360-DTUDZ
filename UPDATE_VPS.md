# 🚀 Hướng dẫn Update Code lên VPS

## 📥 **Quick Update (Recommended)**

Sau khi push code mới lên GitHub, chạy trên VPS:

```bash
# 1. SSH vào VPS
ssh root@34.85.44.142

# 2. Pull code mới
cd /home/flashpanel/CityResQ360-DTUDZ
git pull origin feature/admin

# 3. Restart services cần thiết
cd /opt/cityresq360

# Nếu sửa deploy.sh hoặc docker-compose
cp /home/flashpanel/CityResQ360-DTUDZ/deploy.sh .
cp /home/flashpanel/CityResQ360-DTUDZ/docker-compose.production.yml .

# Nếu sửa CoreAPI (Laravel)
docker-compose -f docker-compose.production.yml restart coreapi

# Nếu sửa services khác
docker-compose -f docker-compose.production.yml restart <service-name>
```

---

## 🔄 **Update Commands Cheat Sheet**

### **Laravel CoreAPI**
```bash
# Restart CoreAPI
docker-compose -f docker-compose.production.yml restart coreapi

# Rebuild CoreAPI (nếu sửa Dockerfile)
docker-compose -f docker-compose.production.yml up -d --build coreapi

# Chạy migrations
docker exec cityresq-coreapi php artisan migrate --force

# Chạy seeders
docker exec cityresq-coreapi php artisan db:seed --force

# Clear cache
docker exec cityresq-coreapi php artisan config:cache
docker exec cityresq-coreapi php artisan route:cache
docker exec cityresq-coreapi php artisan view:cache
```

### **Node.js Services**
```bash
# Restart một service
docker-compose -f docker-compose.production.yml restart incident-service

# Rebuild một service (nếu sửa package.json hoặc Dockerfile)
docker-compose -f docker-compose.production.yml up -d --build incident-service

# Xem logs
docker logs cityresq-incident-service --tail 100 -f
```

### **Python Services**
```bash
# Restart một service
docker-compose -f docker-compose.production.yml restart aiml-service

# Rebuild một service
docker-compose -f docker-compose.production.yml up -d --build aiml-service
```

---

## 🆕 **Full Re-deployment**

Chỉ dùng khi cần clean deployment:

```bash
# SSH vào VPS
ssh root@34.85.44.142

# Pull code mới
cd /home/flashpanel/CityResQ360-DTUDZ
git pull origin feature/admin

# Chạy lại deploy script
sudo ./deploy.sh
```

**Lưu ý:** Script sẽ hỏi:
```
Bạn có muốn down containers cũ trước khi rebuild? (y/N):
```
- `N` → Giữ containers, chỉ rebuild nếu cần (Recommended)
- `y` → Down và rebuild lại toàn bộ

---

## 🗑️ **Clean Deployment (Reset toàn bộ)**

**⚠️ CẢNH BÁO:** Sẽ **XÓA HẾT DATA**!

```bash
ssh root@34.85.44.142

# Stop và xóa containers + volumes
cd /opt/cityresq360
docker-compose -f docker-compose.production.yml down -v

# Xóa toàn bộ Docker system
docker system prune -af --volumes

# Xóa thư mục project
rm -rf /opt/cityresq360

# Pull code mới và deploy
cd /home/flashpanel/CityResQ360-DTUDZ
git pull origin feature/admin
sudo ./deploy.sh
```

---

## 🔍 **Debug Commands**

```bash
# Xem logs service
docker logs cityresq-coreapi --tail 100 -f

# Xem Laravel logs
docker exec cityresq-coreapi cat /var/www/html/storage/logs/laravel.log

# Vào container để debug
docker exec -it cityresq-coreapi sh

# Check environment variables
docker exec cityresq-coreapi env | grep DB_

# Check services status
docker-compose -f docker-compose.production.yml ps
```

---

## 📊 **Health Check**

```bash
# Check all services
curl -I https://api.midstack.io.vn/up

# Check specific endpoints
curl -I https://api.midstack.io.vn/api/v1/health
curl -I https://api.midstack.io.vn/admin/login

# Check service ports (from VPS)
curl -I http://localhost:8000
curl -I http://localhost:8001
curl -I http://localhost:8002
```

---

## 🎯 **Common Update Scenarios**

### **Scenario 1: Sửa code Laravel (Controllers, Models, Routes)**
```bash
cd /home/flashpanel/CityResQ360-DTUDZ
git pull origin feature/admin
cd /opt/cityresq360
docker-compose -f docker-compose.production.yml restart coreapi
docker exec cityresq-coreapi php artisan config:cache
```

### **Scenario 2: Sửa Dockerfile hoặc dependencies**
```bash
cd /home/flashpanel/CityResQ360-DTUDZ
git pull origin feature/admin
cd /opt/cityresq360
cp /home/flashpanel/CityResQ360-DTUDZ/CoreAPI/Dockerfile CoreAPI/
docker-compose -f docker-compose.production.yml up -d --build coreapi
```

### **Scenario 3: Thêm migrations mới**
```bash
cd /home/flashpanel/CityResQ360-DTUDZ
git pull origin feature/admin
docker exec cityresq-coreapi php artisan migrate --force
```

### **Scenario 4: Sửa .env variables**
```bash
cd /opt/cityresq360
nano .env  # Edit variables
docker-compose -f docker-compose.production.yml restart coreapi
```

### **Scenario 5: Sửa Nginx config**
```bash
cd /home/flashpanel/CityResQ360-DTUDZ
git pull origin feature/admin
sudo cp nginx/nginx.conf /etc/nginx/sites-available/cityresq360
sudo nginx -t
sudo systemctl reload nginx
```

