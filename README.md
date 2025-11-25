# 🌆 CityResQ360 — Chung tay vì một đô thị thông minh & an toàn

![Banner](/static/img/Banner.png)

> _"Kết nối người dân - Chính quyền - Công nghệ"_

**CityResQ360**! Đây là dự án mã nguồn mở được xây dựng với mong muốn giúp thành phố trở nên an toàn và đáng sống hơn. CityResQ360 là cầu nối giúp người dân phản ánh nhanh các sự cố (như kẹt xe, ngập lụt, hỏa hoạn...) và giúp chính quyền tiếp nhận, xử lý thông tin kịp thời nhờ sự hỗ trợ của AI.

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

## ✨ Có gì đặc biệt?

![ChucNang](/static/img/chucnang.png)

- **AI thông minh:** Tự động phân tích hình ảnh để phân loại sự cố (cháy, ngập, tai nạn...) giúp giảm tải cho con người.
- **Bản đồ Realtime:** Sự cố hiển thị ngay lập tức trên bản đồ, trực quan sinh động.
- **CityPoint:** Cơ chế điểm thưởng để khuyến khích mọi người cùng đóng góp.
- **Đa nền tảng:** App mobile cho người dân, Web dashboard chuyên nghiệp cho quản lý.

---

## 🛠️ Công nghệ sử dụng

Hệ thống được xây dựng dựa trên các công nghệ hiện đại:

![KienTruc](/static/img/kientruc.png)

Hệ thống được thiết kế theo kiến trúc Microservices hiện đại, đảm bảo khả năng mở rộng và xử lý dữ liệu lớn:

| Thành phần         | Công nghệ sử dụng                                                   |
| :----------------- | :------------------------------------------------------------------ |
| **Mobile App**     | `React Native` (iOS & Android)                                      |
| **Web Dashboard**  | `VueJS`                                                             |
| **Backend Core**   | `Laravel` (PHP), `Redis` (Cache)                                    |
| **AI Services**    | `FastAPI` (Python) cho NLP & Computer Vision                        |
| **API Gateway**    | `Traefik`, `Keycloak` (Auth)                                        |
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

Dự án này tuân theo bộ quy tắc ứng xử cho cộng đồng. Xem file [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) để biết thêm chi tiết về các quy tắc và hành vi được chấp nhận.

## 🤝 Đóng Góp Cho Dự Án

Dự án này là **Open Source**, nên rất hoan nghênh cộng đồng tham gia đóng góp!

### 🌱 Quy Trình Đóng Góp

**1. Fork Repository**

```bash
# Fork repository trên GitHub
# Clone về máy local
git clone https://github.com/Truongpyeo/CityResQ360-DTUDZ.git
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

### 📋 Các Cách Đóng Góp Khác

- Thấy lỗi? 👉 Tạo [Issue](https://github.com/Truongpyeo/CityResQ360-DTUDZ/issues)
- Muốn thêm tính năng? 👉 Fork và gửi Pull Request
- Muốn trao đổi thêm? 👉 Liên hệ qua email bên dưới

Xem thêm hướng dẫn đóng góp tại [CONTRIBUTING.md](CONTRIBUTING.md).

---

## 📄 Giấy Phép

Dự án này được phân phối dưới [GNU General Public License v3.0](LICENSE). Xem file `LICENSE` để biết thêm chi tiết.

## 📞 Liên hệ Team DTU-DZ

Nếu cần trao đổi gì thêm, vui lòng liên hệ:

- **Lê Thanh Trường**: thanhtruong23111999@gmail.com
- **Nguyễn Văn Nhân**: vannhan130504@gmail.com
- **Nguyễn Ngọc Duy Thái**: kkdn011@gmail.com

---

© 2025 CityResQ360 – Code with ❤️ by DTU-DZ Team
