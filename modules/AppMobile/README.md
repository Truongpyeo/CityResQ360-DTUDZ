# CityResQ360

CityResQ360 là nền tảng phản ánh – cảnh báo – giám sát đô thị mở giúp người dân, chính quyền và AI phối hợp xử lý sự cố theo thời gian thực. Dữ liệu tuân thủ chuẩn NGSI-LD nên rất dễ mở API cho đô thị thông minh khác hoặc startup GovTech tích hợp.

## 🚨 Giới thiệu nhanh
- Citizen App (web/mobile web) gửi phản ánh kèm ảnh, video, GPS, mô tả và theo dõi trạng thái xử lý.
- AI Recognition Engine (Vision + NLP) tự động nhận dạng mức khẩn cấp, gợi ý SLA, đơn vị phụ trách và thông điệp broadcast.
- Admin Dashboard giúp city officer quan sát bản đồ realtime, phân công lực lượng, khóa SLA và xem nhật ký xử lý.
- CityWallet + CivicPoint token hóa điểm thưởng, tạo bảng xếp hạng “Citizen Hero”, minh bạch mọi giao dịch.
- Mapbox/Leaflet hiển thị điểm phản ánh, cảnh báo đỏ – vàng – xanh, heatmap mật độ sự cố; sẵn sàng chia sẻ dữ liệu NGSI-LD.

## 🧩 Bốn mô-đun chính
### 1. Citizen App
- Gửi phản ánh đa phương tiện, gắn GPS tự động, nhận thông báo tiến độ.
- CivicPoint thưởng cho phản ánh hữu ích, hiển thị leaderboard theo phường.

### 2. AI Recognition Engine
- Vision AI nhận diện kẹt xe, rác, ngập, cháy nổ… từ ảnh người dân.
- NLP AI xử lý tiếng Việt, phân loại sự cố, suy luận mức khẩn cấp và gợi ý SLA.

### 3. Admin Dashboard
- Bản đồ realtime + bảng SLA + nhật ký hành động cho từng phường/tuyến.
- Bộ lọc theo khu vực, loại sự cố, cấp độ cảnh báo; phân quyền đa vai trò.

### 4. CityWallet + CivicPoint
- Token thưởng cho cư dân; bảng xếp hạng “Citizen Hero”.
- API mở để doanh nghiệp/đơn vị tài trợ tạo ưu đãi hoặc quỹ cộng đồng.

## 🎯 Nhân vật & màn hình tiêu biểu
### 👥 Citizen App
| Screen | Mô tả | Thành phần |
|--------|-------|-----------|
| `/` | Trang giới thiệu, luồng mô-đun | Hero, Module grid, CTA |
| `/map` | Bản đồ realtime (Mapbox) hiển thị phản ánh + heatmap | Map container, overlays |
| `/actions` | Chiến dịch hành động + CivicPoint | ActionCard, FilterBar |
| `/feedback` | Gửi ý tưởng/kịch bản phản ánh | Upload form |
| `/stats` | Thống kê SLA, mật độ sự cố | Recharts (bar/radar/line) |

### 🤖 AI Recognition Engine
| Screen | Mô tả | Thành phần |
|--------|-------|-----------|
| `/chat` | CivicAI Copilot mô phỏng trả lời, gợi ý quy trình | Chat UI, typing indicator |
| `/recommendations` | Kịch bản AI đề xuất (alert template, SLA) | Recommendation list |

### 🏛️ Admin Dashboard
| Screen | Mô tả | Thành phần |
|--------|-------|-----------|
| `/admin/dashboard` | Tổng quan phản ánh, sensor, heatmap | Cards, charts |
| `/admin/wards` | Quản lý dữ liệu từng phường | Table, detail drawer |
| `/admin/ai` | Theo dõi kết quả AI | Heatmap, Insight cards |
| `/admin/users` | Quản lý tài khoản & quyền | CRUD table |
| `/admin/logs` | Nhật ký hệ thống | Timeline, filters |

### 💠 CityWallet + CivicPoint
| Screen | Mô tả |
|--------|-------|
| `/actions` | Danh sách chiến dịch nhận CivicPoint |
| `/stats` | Leaderboard & heatmap điểm thưởng |
| `/feedback` | Đề xuất chiến dịch/ý tưởng mới |

## 🛠 Tech Stack
- Next.js 15.2.3 + TypeScript
- Tailwind CSS 4, custom components (shadcn patterns)
- ApexCharts & Recharts
- Mapbox GL (Cesium optional)
- React Dropzone, Flatpickr, Zustand, React Query

## 📦 Cài đặt
```bash
git clone <repository-url>
cd CityResQ360-DTUDZ/app
yarn install
```

Tạo `.env.local`:
```env
DATABASE_URL="postgresql://..."
NEXT_PUBLIC_MAPBOX_TOKEN=your_mapbox_token
NEXT_PUBLIC_AI_API_KEY=your_ai_api_key
NEXT_PUBLIC_API_URL=http://localhost:3000
JWT_SECRET=your_jwt_secret
```

```bash
yarn prisma:migrate
yarn dev
# mở http://localhost:3000
```

## 🌐 Routes
```
/                           # Landing CityResQ360
/map                        # Bản đồ phản ánh realtime
/actions                    # Chiến dịch & CivicPoint
/feedback                   # Gửi ý tưởng / đề xuất
/stats                      # Thống kê SLA, heatmap

/school/*                   # (Demo) module đào tạo đô thị
/admin/*                    # Dashboard quản trị

/chat                       # CivicAI Copilot
/recommendations            # Kịch bản AI đề xuất
```

## 📱 Theme & Responsive
- Light/Dark mode, lưu preference cục bộ.
- Responsive đầy đủ: desktop ≥1920, laptop ≥1024, tablet ≥768, mobile ≥320.

## 🚦 Scripts
- `yarn dev` / `yarn build` / `yarn start`
- `yarn lint`
- `yarn prisma:generate`, `yarn prisma:migrate`, `yarn prisma:seed`

## 🔐 Roles
- **Citizen**: gửi phản ánh, xem bản đồ, nhận CivicPoint.
- **School / Partner**: (demo) quản lý chương trình cộng đồng.
- **Admin**: quản trị dữ liệu, dashboard, logs.

## 🗄 Database (Prisma + PostgreSQL)
- Users & Roles
- Incidents / Reports / Attachments
- CivicPoint Transactions
- Sensors & AI insights
- Feedback & Campaigns

## 📊 Feature highlights
- Bản đồ realtime với cảnh báo đỏ – vàng – xanh, heatmap mật độ sự cố.
- AI Recognition (Vision + NLP) mô phỏng pipeline tự động đánh giá mức khẩn cấp.
- CityCopilot trợ lý chat hỗ trợ viết biên bản, đề xuất quy trình xử lý.
- CityWallet + CivicPoint gamification minh bạch, leaderboard Citizen Hero.
- Admin analytics: Dashboard SLA, logs, phân quyền đa tầng.

## 🤝 Contributing
1. Fork repo
2. `git checkout -b feature/<name>`
3. Commit + push
4. Mở Pull Request

## 📄 License
Phân phối theo MIT License – xem `LICENSE`.
