## 2025-11-24

- Thêm Dockerfile placeholder cho các service còn thiếu (`AIMLService`, `AnalyticsService`, `FloodEyeService`, `IncidentService`, `IoTService`, `SearchService`) để bảo đảm `docker-compose.production.yml` build thành công.
- Điều chỉnh script `deploy.sh`:
  - Tự động tạo `.env` với các mật khẩu được bao bằng dấu ngoặc kép để tránh lỗi ký tự đặc biệt.
  - Bỏ bước `export` thủ công vì `docker-compose --env-file` đã đủ, tránh lỗi `not a valid identifier`.
- Cập nhật `nginx/nginx.conf` loại bỏ cảnh báo `http2` và chuẩn bị sẵn cấu hình cho Certbot (thay đổi liên quan đến các commit `Fix nginx Error SSL*`).


