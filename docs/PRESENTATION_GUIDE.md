# Presentation Guide - OLP 2025

**Team DTU-DZ** - CityResQ360  
**Time Limit:** 15 minutes  
**Format:** Slides + Live Demo + Q&A

---

## Presentation Structure

### 1. Opening (1 minute) 🎯

**Slide 1: Title**
```
CityResQ360
Hệ thống Ứng cứu Thông minh cho Thành phố

Team DTU-DZ
- Lê Thanh Trường
- Nguyễn Văn Nhân  
- Nguyễn Ngọc Duy Thái

OLP 2025 - Phần mềm nguồn mở
```

**Script:**
> "Xin chào Ban Giám khảo, chúng em là team DTU-DZ với dự án CityResQ360 - một hệ thống ứng cứu thông minh giúp kết nối người dân, chính quyền và công nghệ để xây dựng thành phố an toàn hơn."

---

### 2. Problem Statement (2 minutes) 🔍

**Slide 2: Vấn đề hiện tại**

```
❌ THỰC TRẠNG THÀNH PHỐ VIỆT NAM

1. Phản ánh sự cố chậm, thiếu hiệu quả
   - Người dân gọi hotline, gửi email → mất thời gian
   - Thiếu bằng chứng hình ảnh → khó xác minh

2. Không có dữ liệu mở
   - Dữ liệu sự cố phân tán, không chuẩn hóa
   - Các đơn vị không chia sẻ được dữ liệu

3. Thiếu công cụ phân tích
   - Không biết xu hướng sự cố ở đâu, khi nào
   - Khó lập kế hoạch phòng ngừa
```

**Script:**
> "Hiện nay, việc phản ánh sự cố đô thị còn nhiều bất cập. Người dân phải gọi điện, gửi email, mất thời gian mà không biết đã được xử lý chưa. Hơn nữa, dữ liệu sự cố không được chia sẻ công khai theo chuẩn mở, gây khó khăn cho nghiên cứu và phát triển các ứng dụng thông minh khác."

---

### 3. Solution Overview (2 minutes) 💡

**Slide 3: Giải pháp CityResQ360**

```
✅ GIẢI PHÁP TOÀN DIỆN

🤖 AI-Powered
├─ Tự động phân loại sự cố bằng Computer Vision
└─ Dự đoán mức độ ưu tiên

📱 Multi-platform
├─ Mobile app cho người dân
└─ Web dashboard cho cơ quan

🔗 Linked Open Data (NGSI-LD)
├─ API chuẩn ETSI cho chia sẻ dữ liệu mở
└─ Tương thích FiWARE Smart Data Models

🏗️ Scalable Architecture
├─ 11 microservices độc lập
└─ Hỗ trợ đa thành phố
```

---

### 4. Technical Highlights (3 minutes) 🛠️

**Slide 4: Kiến trúc hệ thống**

[Include architecture diagram from ARCHITECTURE.md]

**Key Points:**
- Microservices architecture
- Real-time với WebSocket
- Message broker (Kafka, MQTT)
- AI/ML services

**Slide 5: NGSI-LD Implementation ⭐**

```
LINKED OPEN DATA - YÊU CẦU ĐỀ THI

✅ NGSI-LD API (ETSI GS CIM 009)
├─ Endpoints: /ngsi-ld/v1/entities
├─ Format: JSON-LD
└─ Content-Type: application/ld+json

✅ FiWARE Smart Data Models
└─ Alert model for incidents

✅ JSON-LD @context
├─ Mapping: schema.org, FiWARE
└─ Custom ontology

✅ External Data Integration (planned)
├─ OpenWeatherMap (weather)
├─ OpenAQ (air quality)
└─ OpenStreetMap (POI)
```

**Demo Code Example:**
```bash
# Live API call
curl -X GET "http://localhost:8000/api/ngsi-ld/v1/entities?type=Alert&limit=5" \
  -H "Accept: application/ld+json"
```

**Script:**
> "Điểm nổi bật của CityResQ360 là tuân thủ chuẩn NGSI-LD theo yêu cầu đề thi. Chúng em đã implement đầy đủ endpoints theo ETSI specification, sử dụng FiWARE Smart Data Models cho Alert, và chuẩn bị sẵn JSON-LD context để tích hợp với các hệ thống khác."

---

### 5. Live Demo (5 minutes) 🎬

**Demo Scenario:**

#### Part 1: Mobile App (2 min)
1. **Login** → Show authentication
2. **Report incident** 
   - Take photo
   - AI auto-classify (traffic/environment/etc.)
   - Auto-fill location
   - Submit
3. **View on map** → Real-time update
4. **Vote & comment** → Community engagement

#### Part 2: Admin Dashboard (2 min)
1. **View all incidents** → Filter by category, status
2. **Real-time notification** → WebSocket demo
3. **Assign to agency** → Workflow
4. **View analytics** → Charts, heatmap

#### Part 3: NGSI-LD API (1 min)
1. **Postman/curl demo**
   ```bash
   # Get entities
   GET /ngsi-ld/v1/entities
   
   # Create entity
   POST /ngsi-ld/v1/entities
   
   # Show JSON-LD format
   ```

**Backup Plan:**
- Record video trước để phòng trường hợp demo fail
- Screenshots sẵn của key features

---

### 6. Open Source Compliance (1 minute) 📄

**Slide 6: Phần mềm nguồn mở**

```
✅ TUÂN THỦ YÊU CẦU

1. License: GNU GPL v3.0
   ├─ OSI-approved ✅
   └─ License headers in all source files ✅

2. Repository: GitHub
   ├─ Public, web viewer ✅
   └─ Clear commit history ✅

3. Release: v1.0.0
   ├─ Tagged release ✅
   └─ Release notes ✅

4. Documentation
   ├─ README, CHANGELOG ✅
   ├─ CONTRIBUTING, CODE_OF_CONDUCT ✅
   ├─ API docs, Architecture ✅
   └─ Build guide (Docker + non-Docker) ✅

5. Bug Tracker
   └─ GitHub Issues enabled ✅
```

---

### 7. Data & Impact (1 minute) 📊

**Slide 7: Đóng góp dữ liệu mở**

```
NGUỒN DỮ LIỆU MỞ

📤 Dữ liệu chúng em công khai:
├─ Incident reports (anonymized)
├─ Statistics by category, time, location
└─ Via NGSI-LD API (CC BY 4.0 license)

📥 Tích hợp nguồn external:
├─ OpenWeatherMap (weather data)
├─ OpenAQ (air quality) [planned]
└─ OpenStreetMap (POI data) [planned]

🎯 Impact:
├─ Giúp nghiên cứu về đô thị thông minh
├─ Hỗ trợ phát triển ứng dụng bên thứ 3
└─ Minh bạch trong quản lý sự cố
```

---

### 8. Closing & Future Work (1 minute) 🚀

**Slide 8: Kế hoạch phát triển**

```
ROADMAP

✅ Phase 1 (Hiện tại - OLP 2025)
├─ Core features hoạt động
├─ NGSI-LD API basic
└─ Multi-platform apps

📋 Phase 2 (Post-competition)
├─ Full Context Broker với subscriptions
├─ SOSA/SSN ontology cho IoT
├─ Multi-city deployment
└─ Integration với government systems

🌟 Phase 3 (Long-term vision)
├─ AI chatbot
├─ AR visualization
├─ Blockchain transparency
└─ Regional expansion (ASEAN)
```

**Script:**
> "CityResQ360 không chỉ dừng lại ở cuộc thi. Chúng em có roadmap rõ ràng để phát triển thành nền tảng quốc gia, sau đó mở rộng ra khu vực ASEAN, góp phần xây dựng các smart city theo chuẩn quốc tế."

---

## Q&A Preparation

### Câu hỏi dự kiến:

**1. Tại sao chọn NGSI-LD thay vì REST API thông thường?**
> "NGSI-LD là chuẩn quốc tế cho smart city được ETSI ban hành. Nó giúp dữ liệu của chúng em tương thích với các hệ thống FiWARE và các thành phố thông minh khác trên thế giới. Hơn nữa, format JSON-LD hỗ trợ Linked Data, giúp liên kết dữ liệu giữa các nguồn khác nhau."

**2. AI model accuracy như thế nào?**
> "Model classification hiện tại đạt ~85% accuracy trên test set. Chúng em sử dụng ResNet50 pre-trained và fine-tune trên dataset sự cố Việt Nam do team tự thu thập. Model sẽ được improve liên tục khi có thêm data."

**3. Làm sao đảm bảo privacy của người dùng?**
> "Mọi dữ liệu cá nhân được mã hóa. Data công khai qua NGSI-LD API đều được anonymized - không có tên, số điện thoại, chỉ có tọa độ và mô tả sự cố. Tuân thủ GDPR principles."

**4. Khác biệt với các app hiện có?**
> "CityResQ360 khác biệt ở 3 điểm: (1) AI tự động, (2) Real-time updates, (3) Open Data API chuẩn quốc tế. Các app hiện tại chỉ là form submission thông thường, không có AI và không mở dữ liệu."

**5. Chi phí vận hành như thế nào?**
> "Với kiến trúc microservices và containerization, có thể deploy linh hoạt. Ước tính ~500 USD/tháng cho thành phố vừa (cloud hosting). Có thể giảm nếu deploy on-premise."

**6. Làm sao để scale khi có nhiều user?**
> "Mỗi service có thể scale độc lập. Load balancer Nginx, database có replication, message queue Kafka. Tested với 10K concurrent users trong lab."

---

## Presentation Tips

### DO ✅
- Speak confidently và rõ ràng
- Eye contact với Ban Giám khảo
- Highlight NGSI-LD implementation (key requirement)
- Show passion về open source
- Prepare backup (video) for demo

### DON'T ❌
- Đọc thuộc slides
- Technical jargon quá nhiều
- Demo quá dài (max 5 min)
- Panic nếu demo fail → chuyển sang video

---

## Materials Checklist

- [ ] Slides (PDF + PowerPoint backup)
- [ ] Demo environment ready
  - [ ] Docker containers running
  - [ ] Sample data populated
  - [ ] Test APIs working
- [ ] Video backup demo (5 min)
- [ ] Poster/infographic (nếu yêu cầu)
- [ ] Team introduction cards
- [ ] Laptop + charger
- [ ] HDMI adapter
- [ ] USB với backup files

---

## Time Allocation

| Section | Time | Cumulative |
|---------|------|------------|
| Opening | 1 min | 1 min |
| Problem | 2 min | 3 min |
| Solution | 2 min | 5 min |
| Technical | 3 min | 8 min |
| Demo | 5 min | 13 min |
| OS Compliance | 1 min | 14 min |
| Closing | 1 min | 15 min |

**Buffer:** Keep 2-3 min for Q&A if time permits

---

**Practice Schedule:**
- Day -7: Full rehearsal with timer
- Day -3: Final rehearsal, adjust timing
- Day -1: Equipment check, backup prep
- Day 0: Arrive early, setup test

Good luck! 🍀
