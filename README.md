# 🌆 CityResQ360 — Chung tay vì một đô thị thông minh & an toàn

[![License: GPL-3.0](https://img.shields.io/badge/License-GPL%203.0-blue.svg)](LICENSE)

[🤝 Đóng Góp](CONTRIBUTING.md) •
[📜 Changelog](CHANGELOG.md)

![Banner](/static/img/Banner.png)

> _"Kết nối người dân - Chính quyền - Công nghệ"_

## 📖 Tổng Quan

**CityResQ360** là một mã nguồn mở, được phát triển bởi đội **DTU-DZ** đến từ **Đại học Duy Tân** để tham gia cuộc thi **Olympic Tin học Sinh viên - Mã nguồn mở năm 2025**. Dự án được thiết kế trong lĩnh vực **xây dựng đô thị thông minh và an toàn**, với các mục tiêu:

🔗 **Kết nối người dân - chính quyền - công nghệ** một cách hiệu quả  
📊 **Quản lý và xử lý sự cố đô thị** một cách chuyên nghiệp  
💡 **Mang lại sự an toàn** cho cộng đồng thành phố  
📝 **Minh bạch dữ liệu** trong quá trình phản ánh và xử lý sự cố

Dự án tập trung vào việc xây dựng một nền tảng toàn diện, kết hợp công nghệ hiện đại như **AI**, **IoT**, **Blockchain** và xử lý dữ liệu thời gian thực để tạo ra một hệ sinh thái đô thị thông minh minh bạch và hiệu quả.

---

## 🤔 Tại sao lại có dự án này?

Chúng ta đều thấy thành phố ngày càng đông đúc, và các vấn đề như kẹt xe, ngập nước hay tai nạn xảy ra thường xuyên hơn. Tuy nhiên:

- Việc báo tin đôi khi còn thủ công, chậm trễ.
- Thông tin đến cơ quan chức năng nhiều khi không đầy đủ hoặc bị trôi.
- Thiếu một cái nhìn tổng quan, thời gian thực về những gì đang diễn ra.

CityResQ360 ra đời để giải quyết những vấn đề đó, hướng tới một quy trình xử lý nhanh hơn, minh bạch hơn và thông minh hơn.

---

## 👥 Dự án này dành cho ai?

![DoiTuong](/static/img/doituong.png)

1. **Người dân:** Gửi phản ánh cực nhanh (kèm ảnh, vị trí), nhận cảnh báo nguy hiểm, và tích điểm **CityPoint** đổi quà.
2. **Cơ quan chức năng:** Có công cụ quản lý trực quan, nắm bắt ngay các điểm nóng để điều phối xử lý.
3. **Tình nguyện viên / NGO:** Dễ dàng tiếp cận thông tin để hỗ trợ cộng đồng.
4. **Cộng đồng Developer:** Một sân chơi thú vị để tìm hiểu và ứng dụng công nghệ mới (AI, IoT, Big Data...).

---

## ✨ Chức năng

![ChucNang](/static/img/chucnang.png)

- **AI thông minh:** Tự động phân tích hình ảnh để phân loại sự cố (cháy, ngập, tai nạn...) giúp giảm tải cho con người.
- **Bản đồ Realtime:** Sự cố hiển thị ngay lập tức trên bản đồ, trực quan sinh động.
- **CityPoint:** Cơ chế điểm thưởng để khuyến khích mọi người cùng đóng góp.
- **Đa nền tảng:** App mobile cho người dân, Web dashboard chuyên nghiệp cho quản lý.

---

## 🗺️ Kiến Trúc Hệ Thống (System Architecture)

![KienTruc](/static/img/kientruc.png)

Hệ thống được thiết kế theo kiến trúc Microservices hiện đại, đảm bảo khả năng mở rộng và xử lý dữ liệu lớn:

| Thành phần         | Công nghệ sử dụng                                                   |
| :----------------- | :------------------------------------------------------------------ |
| **Mobile App**     | `React Native` (iOS & Android)                                      |
| **Web**            | `VueJS`                                                             |
| **Backend Core**   | `Laravel` (PHP), `Redis` (Cache)                                    |
| **AI Services**    | `FastAPI` (Python) cho NLP & Computer Vision                        |
| **API Gateway**    | `Authenticator`                                                     |
| **Message Broker** | `Apache Kafka`, `MQTT` (EMQX/Mosquitto)                             |
| **Realtime**       | `Reverb` (WebSocket)                                                |
| **Database**       | `PostgreSQL` + `PostGIS` (GeoData), `MinIO` (Storage), `OpenSearch` |

---

## 🚀 Cách hoạt động

Quy trình đơn giản như sau:

1. **Người dân** thấy sự cố 📸 -> Chụp ảnh & Gửi qua App.
2. **Hệ thống** nhận tin 🤖 -> AI phân tích ảnh & nội dung -> Đẩy về trung tâm.
3. **Cơ quan chức năng** 👮 -> Nhận tin -> Xử lý -> Cập nhật kết quả.
4. **Người dân** nhận thông báo "Đã xong" ✅ -> Nhận điểm thưởng CityPoint.

---

## 🌱 Hướng phát triển

Dự án không chỉ dừng lại ở việc phản ánh sự cố mà còn hướng tới một hệ sinh thái đô thị thông minh toàn diện:

### 🌐 Mở rộng & Kết nối

- **Quy mô:** Triển khai đa thành phố, hỗ trợ đa ngôn ngữ (Quốc tế hóa).
- **Bản đồ số:** Tích hợp bản đồ chi tiết thời gian thực, hiển thị vùng nguy hiểm và đường di tản.

### 🧠 Nâng cấp AI & Dữ liệu

- **Dự đoán rủi ro:** Phân tích dữ liệu lịch sử và thời tiết để cảnh báo sớm thiên tai, ngập lụt.
- **Computer Vision:** Tự động nhận diện sự cố từ Camera giao thông và UAV.
- **Chatbot thông minh:** Hỗ trợ người dân tra cứu, sơ tán và báo cáo tự động 24/7.

### 🔒 Blockchain & Minh bạch

- **Smart Contract:** Lưu trữ hồ sơ phản ánh vĩnh viễn, không thể chỉnh sửa.
- **Token hóa CityPoint:** Chuyển đổi điểm thưởng thành token có giá trị thực tế trên mạng lưới Polygon/Ethereum.

---

## ⚖️ Quy Tắc Ứng Xử

Dự án này tuân theo bộ quy tắc ứng xử cho cộng đồng. Xem file [CODE_OF_CONDUCT.md](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/blob/feat/document/CODE_OF_CONDUCT.md) để biết thêm chi tiết về các quy tắc và hành vi được chấp nhận.

---

## 🗂️ Cấu trúc dự án (Project Structure)

```
CityResQ360-DTUDZ/
├── .github/                    # GitHub configurations
│   └── ISSUE_TEMPLATE/        # Issue templates
├── modules/                    # 🎯 All microservices & apps
│   ├── CoreAPI/               # Laravel 12 - Core API (Port 8000)
│   ├── AppMobile/             # Next.js - Mobile App (Port 3000)
│   ├── AIMLService/           # Python FastAPI - AI/ML (Port 8003)
│   ├── AnalyticsService/      # Python - Analytics (Port 8009)
│   ├── ContextBroker/         # N GSI-LD Context Broker (Port 1026)
│   ├── FloodEyeService/       # Python - Flood Monitoring (Port 8008)
│   ├── IncidentService/       # Node.js - Incident Management (Port 8001)
│   ├── IoTService/            # Node.js - IoT Sensors (Port 8002)
│   ├── MediaService/          # Node.js - Media Storage (Port 8004)
│   ├── NotificationService/   # Node.js - Notifications (Port 8006)
│   ├── SearchService/         # Python - Search Engine (Port 8007)
│   └── WalletService/         # Go - Wallet & CityPoint (Port 8005)
├── infrastructure/             # ⚙️ Infrastructure configurations
│   ├── docker/                # Docker Compose files
│   │   ├── docker-compose.yml              # Development
│   │   └── docker-compose.production.yml   # Production
│   ├── nginx/                 # Nginx configuration
│   └── mosquitto/             # MQTT Broker configuration
├── collections/                # 📮 API Testing collections
│   └── postman/               # Postman collections
├── docs/                       # 📚 Documentation
│   ├── PROJECT_CONTEXT.md     # Project architecture & context
│   ├── DEVELOPMENT_WORKFLOW.md # Development guidelines
│   └── DOCKER.md              # Docker setup guide
├── scripts/                    # 🛠️ Utility scripts
│   ├── setup/                 # Setup scripts
│   ├── deploy/                # Deployment scripts
│   │   └── deploy.sh          # Main deployment script
│   └── migration/             # Migration & maintenance scripts
├── static/                     # 🖼️ Static assets
│   └── img/                   # Images & diagrams
├── README.md                   # This file
├── LICENSE                     # MIT License
├── CHANGELOG.md               # Version history
├── CODE_OF_CONDUCT.md         # Code of conduct
└── CONTRIBUITING.md           # Contribution guidelines
```

---

## 🤝 Đóng Góp Cho Dự Án

Dự án này là **Open Source**, nên rất hoan nghênh cộng đồng tham gia đóng góp!

### 🌱 Quy Trình Đóng Góp

**1. Fork Repository**

```bash
# Fork repository trên GitHub
# Clone về máy local
git clone https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ
cd CityResQ360-DTUDZ
```

**2. Tạo Branch Mới**

```bash
# Tạo và chuyển sang branch mới
git checkout -b feat/<new-feature>

# Ví dụ
git checkout -b feat/disaster-tracking
```

**3. Commit Thay Đổi**

```bash
# Thêm file đã thay đổi
git add .

# Commit với message rõ ràng
git commit -m "feat: add disaster tracking module"
```

**4. Push Branch**

```bash
# Push lên repository của bạn
git push -u origin feat/<new-feature>
```

**5. Tạo Pull Request**

- Truy cập repository gốc tại GitHub
- Chọn "New Pull Request"
- Chọn branch của bạn để merge
- Điền thông tin mô tả chi tiết

### 📝 Issues

- Báo cáo lỗi và đề xuất tính năng mới tại [GitHub Issues](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/issues)

Xem thêm hướng dẫn đóng góp tại [CONTRIBUTING.md](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/blob/master/CONTRIBUITING.md)

---

## 👥 Người Hướng Dẫn

| 👨‍🏫 Vai Trò | 📧 Thông Tin          |
| ---------- | --------------------- |
| Giảng Viên | Nguyễn Quốc Long      |
| Email      | quoclongdng@gmail.com |

## 📞 Liên hệ Team DTU-DZ

Nếu cần trao đổi gì thêm, vui lòng liên hệ:

- **Lê Thanh Trường**: thanhtruong23111999@gmail.com
- **Nguyễn Văn Nhân**: vannhan130504@gmail.com
- **Nguyễn Ngọc Duy Thái**: kkdn011@gmail.com

---

## 📜 Changelog

Xem [CHANGELOG.md](CHANGELOG.md) để biết lịch sử thay đổi.

## 📄 Giấy Phép

Dự án này được phân phối dưới [GNU General Public License v3.0](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/blob/master/LICENSE). Xem file `LICENSE` để biết thêm chi tiết.

© 2025 CityResQ360 – Code with ❤️ by DTU-DZ Team
