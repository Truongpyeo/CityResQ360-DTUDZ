- Tiếp tục sửa các route Next.js động (`citizen/report/[id]`) để tương thích `PageProps` dạng Promise giúp build production không lỗi.
## 2025-11-24

- Thêm Dockerfile placeholder cho các service còn thiếu (`AIMLService`, `AnalyticsService`, `FloodEyeService`, `IncidentService`, `IoTService`, `SearchService`) để bảo đảm `docker-compose.production.yml` build thành công.
- Điều chỉnh script `deploy.sh`:
  - Tự động tạo `.env` với các mật khẩu được bao bằng dấu ngoặc kép để tránh lỗi ký tự đặc biệt.
  - Bỏ bước `export` thủ công vì `docker-compose --env-file` đã đủ, tránh lỗi `not a valid identifier`.
  - Tự động tạo `WalletService/go.sum` nếu thiếu để Docker build không thất bại.
- Bổ sung `NotificationService` placeholder đầy đủ (package.json, package-lock, server.js) và cập nhật Dockerfile fallback `npm install` để bảo đảm build được dù chưa có code thật.
- Thêm mã placeholder chạy được cho các service phụ trợ:
  - `WalletService` (Go HTTP health endpoint) + dọn gọn `go.mod`/`go.sum`.
  - `IncidentService`, `IoTService` (Express health endpoint + Dockerfile mới).
  - `AIMLService`, `AnalyticsService`, `FloodEyeService`, `SearchService` (FastAPI health endpoint + requirements + Dockerfile chạy Uvicorn).
- Fix lỗi build Next.js (AppMobile) - đã build test thành công:
  - Điều chỉnh tất cả dynamic routes (`users/[id]`, `report/[id]`, `incidents/[id]`, v.v.) tuân thủ typing mới `PageProps` (params dạng Promise).
  - Sửa type error ApexCharts trong `StatsBarChart.tsx` và `StatsLineChart.tsx`: thay `width/height/radius` thành `size` cho `legend.markers`.
  - Sửa type conflict framer-motion trong `shimmer-button.tsx`: dùng `HTMLMotionProps<"button">` thay vì `ButtonHTMLAttributes`.
- Cập nhật `nginx/nginx.conf` loại bỏ cảnh báo `http2` và chuẩn bị sẵn cấu hình cho Certbot (thay đổi liên quan đến các commit `Fix nginx Error SSL*`).
- Hoàn thiện cấu hình domain/email và script deploy production (các commit `Add Domain and Email`, `Docker Deloy Production`).
- Hoàn thiện service RabbitMQ + MediaService + MinIO chạy trong Docker Compose cho production.

## 2025-11-21

- Hoàn thiện nhánh `develop` với nền tảng dịch vụ lõi, đảm bảo build Docker cơ bản chạy được trên môi trường master (`Done Master`).

## 2025-11-18

- Merge chuỗi PR document + frontend:
  - `feat/document`, `feat/app` hợp nhất vào `develop` và `master`.
  - Thêm core frontend (FE Admin Panel) và cập nhật README/docs.
- Cập nhật tài liệu dự án: README, hướng dẫn sử dụng, ảnh minh họa.

## 2025-11-15

- Merge `develop` vào `master` (PR #7) ổn định kiến trúc microservice và tài liệu đi kèm.

## 2025-11-11

- Merge `feature/microservice` vào `master`: bổ sung nền tảng microservices.
- Hoàn thiện FE Admin Panel đầu tiên.

## 2025-11-07

- Cập nhật assets hình ảnh phục vụ document/FE (`fix: add img`).

## 2025-11-06

- Khởi tạo dự án (các commit `Init`).
- Thêm license, changelog, code of conduct, contributing guide.
- Merge `feature/laravel` và `feat/document` vào master để hoàn thiện nền tảng Laravel ban đầu.
