# Laravel Reverb trong Docker với Supervisor

## Tổng quan

Laravel Reverb đã được tích hợp vào Docker container với Supervisor để tự động khởi động và quản lý process.

## Cấu hình Supervisor

### Program: reverb

Trong `Dockerfile`, supervisor đã được config để chạy Reverb:

```ini
[program:reverb]
command=php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/var/www/html
user=www-data
autostart=true
autorestart=true
stdout_logfile=/var/log/supervisor/reverb.log
stdout_logfile_maxbytes=10MB
stderr_logfile=/var/log/supervisor/reverb.error.log
stderr_logfile_maxbytes=10MB
stopwaitsecs=60
stopsignal=TERM
```

### Giải thích các tham số:

- `command`: Chạy Reverb server với host 0.0.0.0 (listen tất cả interfaces) và port 8080
- `directory`: Working directory
- `user`: Chạy dưới user www-data (giống PHP-FPM)
- `autostart=true`: Tự động start khi container khởi động
- `autorestart=true`: Tự động restart nếu process die
- `stdout_logfile`: Log output thông thường
- `stderr_logfile`: Log lỗi
- `stopwaitsecs=60`: Đợi 60s trước khi force kill
- `stopsignal=TERM`: Gửi SIGTERM để graceful shutdown

## Ports

Container expose 2 ports:
- **80**: Nginx (HTTP API)
- **8080**: Reverb WebSocket server

## Logs

### Xem logs Reverb realtime:

```bash
# Trong container
docker exec -it coreapi tail -f /var/log/supervisor/reverb.log

# Hoặc từ docker-compose
docker-compose logs -f coreapi | grep reverb
```

### Xem error logs:

```bash
docker exec -it coreapi tail -f /var/log/supervisor/reverb.error.log
```

### Xem tất cả supervisor logs:

```bash
docker exec -it coreapi tail -f /var/log/supervisor/supervisord.log
```

## Supervisor Control

### Check status của tất cả processes:

```bash
docker exec -it coreapi supervisorctl status
```

Expected output:
```
nginx        RUNNING   pid 123, uptime 0:05:30
php-fpm      RUNNING   pid 124, uptime 0:05:30
reverb       RUNNING   pid 125, uptime 0:05:30
```

### Restart Reverb:

```bash
docker exec -it coreapi supervisorctl restart reverb
```

### Stop Reverb:

```bash
docker exec -it coreapi supervisorctl stop reverb
```

### Start Reverb:

```bash
docker exec -it coreapi supervisorctl start reverb
```

### Reload tất cả configs:

```bash
docker exec -it coreapi supervisorctl reread
docker exec -it coreapi supervisorctl update
```

## Testing trong Docker

### 1. Test từ host machine:

```bash
# Check Reverb có chạy không
curl http://localhost:8080/

# Test WebSocket connection
wscat -c ws://localhost:8080/app/your-app-key
```

### 2. Test từ browser:

Mở `http://localhost:8000/test-realtime.html` và config:
- **Host**: `localhost`
- **Port**: `8080`
- **Token**: (empty nếu chưa có auth)

### 3. Test từ trong container:

```bash
docker exec -it coreapi php artisan tinker

# Trong tinker
use App\Events\NewReportForAdmins;
use App\Models\BaoCao;
$report = BaoCao::first();
$user = $report->nguoiDung;
broadcast(new NewReportForAdmins($report, $user));
```

## Troubleshooting

### 1. Reverb không start:

```bash
# Check logs
docker exec -it coreapi tail -f /var/log/supervisor/reverb.error.log

# Check process status
docker exec -it coreapi supervisorctl status reverb

# Manual start để xem lỗi
docker exec -it coreapi php artisan reverb:start --debug
```

### 2. Connection refused trên port 8080:

```bash
# Check port có được expose không
docker ps | grep coreapi

# Check firewall
docker exec -it coreapi netstat -tulpn | grep 8080

# Check trong docker-compose.yml
# Phải có: 8080:8080 trong ports section
```

### 3. Reverb crash liên tục:

```bash
# Check memory
docker stats coreapi

# Check error logs
docker exec -it coreapi tail -100 /var/log/supervisor/reverb.error.log

# Check env variables
docker exec -it coreapi php artisan config:show broadcasting
```

### 4. Restart toàn bộ services:

```bash
docker exec -it coreapi supervisorctl restart all
```

## Production Considerations

### 1. Scaling Reverb:

Nếu cần scale horizontally, sử dụng Redis để sync giữa các instances:

```env
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb
```

### 2. SSL/TLS:

Trong production, configure nginx làm reverse proxy cho WebSocket với SSL:

```nginx
location /reverb {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
}
```

### 3. Monitoring:

- Monitor supervisor logs với centralized logging (ELK, Loki)
- Setup alerting khi Reverb restart nhiều lần
- Monitor WebSocket connections metrics

### 4. Resource Limits:

Adjust memory/CPU limits trong docker-compose.yml:

```yaml
services:
  coreapi:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
```

## Environment Variables

Các biến quan trọng trong `.env`:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http
```

## Build & Deploy

### Rebuild image với Reverb:

```bash
cd modules/CoreAPI
docker build -t coreapi:latest .
```

### Hoặc dùng docker-compose:

```bash
cd infrastructure/docker
docker-compose up -d --build coreapi
```

### Verify Reverb đang chạy:

```bash
docker-compose logs coreapi | grep -i reverb
docker exec -it coreapi supervisorctl status reverb
```

## Kết luận

✅ **Ưu điểm của việc dùng Supervisor:**
- Tự động start khi container khởi động
- Tự động restart nếu crash
- Centralized logging
- Dễ dàng quản lý multiple processes
- Graceful shutdown

✅ **So với chạy manual:**
- Không cần `php artisan reverb:start` thủ công
- Process được monitor và auto-restart
- Logs được centralized
- Dễ debug và troubleshoot

---

**Documentation Updated**: December 7, 2025  
**Laravel Version**: 11.x  
**Reverb Version**: 1.6.3  
**Supervisor Version**: 4.x (Alpine)
