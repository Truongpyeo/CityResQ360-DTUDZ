# CityResQ360 API Documentation

**Version:** 1.0  
**Base URL:** `{{base_url}}/api/v1`  
**Authentication:** Bearer Token (Laravel Sanctum)

---

## Table of Contents

- [Authentication (Public)](#authentication-public)
- [Authentication (Protected)](#authentication-protected)
- [Reports](#reports)
- [Comments](#comments)
- [Media](#media)
- [Map & Location](#map--location)
- [Agencies](#agencies)
- [User Profile & Stats](#user-profile--stats)
- [Wallet & CityPoints](#wallet--citypoints)
- [Notifications](#notifications)
- [Categories & Priorities](#categories--priorities)
- [Weather Data](#weather-data)
- [Error Codes](#error-codes)

---

## Authentication (Public)

### POST /auth/register

**Đăng ký tài khoản mới**

**Authentication:** None (public)

**Request Body:**

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `ho_ten` | string | ✅ | min:3, max:100 | Họ và tên đầy đủ |
| `email` | string | ✅ | email, unique | Email đăng nhập |
| `so_dien_thoai` | string | ✅ | regex:0[0-9]{9} | Số điện thoại (10 số, bắt đầu bằng 0) |
| `mat_khau` | string | ✅ | min:8 | Mật khẩu (tối thiểu 8 ký tự) |
| `mat_khau_confirmation` | string | ✅ | same:mat_khau | Xác nhận mật khẩu |

**Response 201 Created:**
```json
{
  "success": true,
  "message": "Đăng ký thành công",
  "data": {
    "user": {
      "id": 1,
      "ho_ten": "Nguyễn Văn A",
      "email": "nguyenvana@example.com",
      "so_dien_thoai": "0901234567",
      "vai_tro": 0
    },
    "token": "1|xxxxxxxxxxxxx..."
  }
}
```

**Errors:**
- `422`: Validation failed (email đã tồn tại, số điện thoại không hợp lệ, etc.)

---

### POST /auth/login

**Đăng nhập và nhận Bearer Token**

**Authentication:** None (public)

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Email đăng nhập |
| `mat_khau` | string | ✅ | Mật khẩu |
| `remember` | boolean | ❌ | Ghi nhớ đăng nhập (default: false) |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đăng nhập thành công",
  "data": {
    "user": {
      "id": 1,
      "ho_ten": "Nguyễn Văn A",
      "email": "nguyenvana@example.com",
      "vai_tro": 0
    },
    "token": "1|xxxxxxxxxxxxx..."
  }
}
```

**Errors:**
- `401`: Email hoặc mật khẩu không đúng
- `422`: Validation failed

---

### POST /auth/forgot-password

**Gửi link đặt lại mật khẩu qua email**

**Authentication:** None (public)

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Email tài khoản cần reset |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Link đặt lại mật khẩu đã được gửi đến email của bạn"
}
```

**Errors:**
- `404`: Email không tồn tại
- `429`: Quá nhiều requests, vui lòng thử lại sau

---

### POST /auth/reset-password

**Đặt lại mật khẩu với token**

**Authentication:** None (public)

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `token` | string | ✅ | Token từ email reset password |
| `email` | string | ✅ | Email tài khoản |
| `mat_khau` | string | ✅ | Mật khẩu mới (min: 8 ký tự) |
| `mat_khau_confirmation` | string | ✅ | Xác nhận mật khẩu mới |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đặt lại mật khẩu thành công"
}
```

**Errors:**
- `400`: Token không hợp lệ hoặc đã hết hạn
- `422`: Validation failed

---

## Authentication (Protected)

> **Note:** Tất cả các endpoints dưới đây yêu cầu header `Authorization: Bearer {token}`

### GET /auth/me

**Lấy thông tin user hiện tại**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "ho_ten": "Nguyễn Văn A",
    "email": "nguyenvana@example.com",
    "so_dien_thoai": "0901234567",
    "anh_dai_dien": "https://...",
    "vai_tro": 0,
    "diem_tin_cay": 85.5,
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Errors:**
- `401`: Unauthorized (token không hợp lệ)

---

### POST /auth/logout

**Đăng xuất và revoke token hiện tại**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đăng xuất thành công"
}
```

---

### POST /auth/refresh

**Làm mới token**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "data": {
    "token": "2|yyyyyyyyyyyy..."
  }
}
```

---

### PUT /auth/profile

**Cập nhật thông tin profile**

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `ho_ten` | string | ❌ | Họ và tên đầy đủ |
| `so_dien_thoai` | string | ❌ | Số điện thoại |
| `anh_dai_dien` | string | ❌ | URL ảnh đại diện |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Cập nhật profile thành công",
  "data": {
    "id": 1,
    "ho_ten": "Nguyễn Văn A Updated",
    ...
  }
}
```

---

### POST /auth/change-password

**Đổi mật khẩu**

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `mat_khau_cu` | string | ✅ | Mật khẩu hiện tại |
| `mat_khau_moi` | string | ✅ | Mật khẩu mới (min: 8 ký tự) |
| `mat_khau_moi_confirmation` | string | ✅ | Xác nhận mật khẩu mới |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đổi mật khẩu thành công"
}
```

**Errors:**
- `400`: Mật khẩu cũ không đúng

---

### POST /auth/verify-email

**Xác thực email với mã code**

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `code` | string | ✅ | Mã xác thực 6 số từ email |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Xác thực email thành công"
}
```

**Errors:**
- `400`: Mã code không đúng hoặc đã hết hạn

---

### POST /auth/verify-phone

**Xác thực số điện thoại với mã code**

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `code` | string | ✅ | Mã xác thực 6 số từ SMS |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Xác thực số điện thoại thành công"
}
```

**Errors:**
- `400`: Mã code không đúng hoặc đã hết hạn

---

### POST /auth/update-fcm-token

**Cập nhật FCM token cho push notifications**

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `fcm_token` | string | ✅ | Firebase Cloud Messaging token |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Cập nhật FCM token thành công"
}
```

---

## Reports

### GET /reports

**Lấy danh sách phản ánh (có phân trang và filter)**

**Authentication:** Optional (public reports only if not authenticated)

**Query Parameters:**

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `page` | integer | ❌ | Trang hiện tại (default: 1) | `1` |
| `per_page` | integer | ❌ | Số item/trang (default: 15, max: 100) | `20` |
| `danh_muc_id` | integer | ❌ | Filter theo ID danh mục (1-6) | `1` |
| `trang_thai` | integer | ❌ | Filter theo trạng thái (0-4) | `0` |
| `uu_tien_id` | integer | ❌ | Filter theo ID mức ưu tiên (1-4) | `1` |
| `search` | string | ❌ | Tìm kiếm theo tiêu đề hoặc mô tả | `ổ gà` |
| `sort_by` | string | ❌ | Sắp xếp theo field (allowed: created_at, updated_at, luot_ung_ho, luot_xem) | `created_at` |
| `sort_order` | string | ❌ | Thứ tự sắp xếp (asc/desc, default: desc) | `desc` |

**Categories (danh_muc_id):**
- `1`: Giao thông (Traffic)
- `2`: Môi trường (Environment)
- `3`: Hỏa hoạn (Fire)
- `4`: Rác thải (Waste)
- `5`: Ngập lụt (Flood)
- `6`: Khác (Other)

**Status (trang_thai):**
- `0`: Chờ xác minh (Pending)
- `1`: Đã xác minh (Verified)
- `2`: Đang xử lý (In Progress)
- `3`: Đã giải quyết (Resolved)
- `4`: Từ chối (Rejected)

**Priorities (uu_tien_id):**
- `1`: Thấp (Low)
- `2`: Trung bình (Medium)
- `3`: Cao (High)
- `4`: Khẩn cấp (Urgent)

**Response 200 OK:**
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

---

### POST /reports

**Tạo phản ánh mới**

**Authentication:** Required

**Permissions:** Citizen, Agency, Admin

**Request Body:**

| Field | Type | Required | Validation | Description | Example |
|-------|------|----------|------------|-------------|---------|
| `tieu_de` | string | ✅ | min:3, max:200 | Tiêu đề phản ánh | "Ổ gà trên đường Lê Lợi" |
| `mo_ta` | string | ✅ | min:10, max:1000 | Mô tả chi tiết | "Nhiều ổ gà lớn..." |
| `danh_muc_id` | integer | ✅ | exists:danh_muc_phan_anhs,id | ID danh mục (1-6) | `1` |
| `uu_tien_id` | integer | ❌ | exists:muc_uu_tiens,id | ID mức ưu tiên (default: 2) | `2` |
| `vi_do` | float | ✅ | latitude | Vĩ độ GPS | `10.7769` |
| `kinh_do` | float | ✅ | longitude | Kinh độ GPS | `106.7009` |
| `dia_chi` | string | ✅ | max:255 | Địa chỉ chi tiết | "123 Lê Lợi, Q1, TPHCM" |
| `media_ids` | array | ❌ | exists:hinh_anh_phan_anhs,id | Mảng IDs ảnh/video | `[1, 2, 3]` |
| `la_cong_khai` | boolean | ❌ | - | Public/Private (default: true) | `true` |
| `the_tags` | array | ❌ | - | Mảng tags | `["urgent", "traffic"]` |

**Note:** 
- Upload media trước qua `/media/upload` endpoint để lấy IDs
- `media_ids` chỉ chấp nhận media của chính user tạo

**Response 201 Created:**
```json
{
  "success": true,
  "message": "Tạo phản ánh thành công. Bạn nhận được +10 CityPoints!",
  "data": {
    "id": 123,
    "tieu_de": "Ổ gà trên đường Lê Lợi",
    "mo_ta": "...",
    "danh_muc_id": 1,
    "danh_muc": {
      "id": 1,
      "ten_danh_muc": "Giao thông"
    },
    "trang_thai": 0,
    "uu_tien_id": 2,
    "uu_tien": {
      "id": 2,
      "level": 1,
      "ten_muc": "Trung bình",
      "mau_sac": "#FFA500"
    },
    "vi_do": 10.7769,
    "kinh_do": 106.7009,
    "dia_chi": "123 Lê Lợi, Quận 1",
    "media": [...],
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Errors:**
- `401`: Unauthorized
- `422`: Validation failed

---

### GET /reports/my

**Lấy danh sách phản ánh của tôi**

**Authentication:** Required

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | ❌ | Trang hiện tại (default: 1) |
| `per_page` | integer | ❌ | Số item/trang (default: 15) |
| `trang_thai` | integer | ❌ | Filter theo trạng thái (0-4) |

**Response 200 OK:** Giống `/reports` (paginated)

---

### GET /reports/nearby

**Lấy phản ánh gần vị trí hiện tại**

**Authentication:** Optional

**Query Parameters:**

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `vi_do` | float | ✅ | Vĩ độ vị trí hiện tại | `10.7769` |
| `kinh_do` | float | ✅ | Kinh độ vị trí hiện tại | `106.7009` |
| `radius` | float | ❌ | Bán kính tìm kiếm (km, default: 5) | `5` |

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tieu_de": "...",
      "distance": 0.5,
      ...
    }
  ]
}
```

_Note: Kết quả được sắp xếp theo khoảng cách gần nhất, giới hạn 50 items_

---

### GET /reports/trending

**Lấy phản ánh đang trending (nhiều upvote/view nhất trong 7 ngày)**

**Authentication:** Optional

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `limit` | integer | ❌ | Số lượng kết quả (default: 10, max: 50) |

**Response 200 OK:**
```json
{
  "success": true,
  "data": [...]
}
```

---

### GET /reports/{id}

**Lấy chi tiết phản ánh**

**Authentication:** Optional (required for private reports)

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID phản ánh |

**Response 200 OK:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "tieu_de": "...",
    "mo_ta": "...",
    "danh_muc": {...},
    "uu_tien": {...},
    "trang_thai": 0,
    "vi_do": 10.7769,
    "kinh_do": 106.7009,
    "dia_chi": "...",
    "luot_ung_ho": 25,
    "luot_khong_ung_ho": 2,
    "luot_xem": 150,
    "user": {...},
    "agency": {...},
    "votes": {
      "total_upvotes": 25,
      "total_downvotes": 2,
      "user_voted": "upvote"
    },
    "comments": [...],
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Errors:**
- `404`: Report không tồn tại
- `403`: Report private và user không phải owner

---

### PUT /reports/{id}

**Cập nhật phản ánh (chỉ owner)**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID phản ánh |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tieu_de` | string | ❌ | Tiêu đề mới |
| `mo_ta` | string | ❌ | Mô tả mới |
| `uu_tien_id` | integer | ❌ | ID mức ưu tiên mới |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Cập nhật phản ánh thành công",
  "data": {...}
}
```

**Errors:**
- `403`: Không phải owner
- `404`: Report không tồn tại

---

### DELETE /reports/{id}

**Xóa phản ánh (chỉ owner)**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID phản ánh |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Xóa phản ánh thành công",
  "data": {
    "id": 1,
    "deleted": true
  }
}
```

**Errors:**
- `400`: Không thể xóa report đang/đã xử lý
- `403`: Không phải owner
- `404`: Report không tồn tại

---

### POST /reports/{id}/vote

**Vote phản ánh (upvote/downvote)**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID phản ánh |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `loai_binh_chon` | string | ✅ | Loại vote: "upvote" or "downvote" |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Vote thành công",
  "data": {
    "total_upvotes": 26,
    "total_downvotes": 2,
    "user_voted": "upvote"
  }
}
```

_Note: User có thể đổi vote, nhưng không thể vote lại cùng loại_

---

### POST /reports/{id}/view

**Tăng số lượt xem**

**Authentication:** Optional

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID phản ánh |

**Response 200 OK:**
```json
{
  "success": true,
  "data": {
    "luot_xem": 151
  }
}
```

---

### POST /reports/{id}/rate

**Đánh giá phản ánh sau khi đã giải quyết (chỉ owner)**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID phản ánh |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `danh_gia_hai_long` | integer | ✅ | Đánh giá hài lòng (1-5 sao) |
| `nhan_xet` | string | ❌ | Nhận xét (max: 500 ký tự) |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đánh giá thành công. Cảm ơn bạn!",
  "data": {
    "danh_gia_hai_long": 5,
    "nhan_xet": "Giải quyết nhanh chóng"
  }
}
```

**Errors:**
- `400`: Chỉ đánh giá được report đã resolved
- `403`: Chỉ owner mới đánh giá được

---

## Comments

### GET /reports/{report_id}/comments

**Lấy danh sách bình luận của phản ánh**

**Authentication:** Optional

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `report_id` | integer | ID phản ánh |

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "noi_dung": "Cảm ơn bạn đã phản ánh",
      "user": {
        "id": 1,
        "ho_ten": "...",
        "anh_dai_dien": "..."
      },
      "luot_thich": 5,
      "created_at": "..."
    }
  ]
}
```

---

(Continued in next section...)
### POST /reports/{report_id}/comments

**Tạo bình luận mới**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `report_id` | integer | ID phản ánh |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `noi_dung` | string | ✅ | Nội dung bình luận (max: 1000 ký tự) |

**Response 201 Created:**
```json
{
  "success": true,
  "message": "Bình luận thành công",
  "data": {
    "id": 1,
    "noi_dung": "...",
    "user": {...},
    "created_at": "..."
  }
}
```

---

### PUT /comments/{id}

**Cập nhật bình luận (chỉ owner)**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID bình luận |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `noi_dung` | string | ✅ | Nội dung mới |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Cập nhật bình luận thành công",
  "data": {...}
}
```

---

### DELETE /comments/{id}

**Xóa bình luận (chỉ owner)**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID bình luận |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Xóa bình luận thành công"
}
```

---

### POST /comments/{id}/like

**Thích bình luận**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID bình luận |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đã thích bình luận",
  "data": {
    "luot_thich": 6
  }
}
```

---

### POST /comments/{id}/unlike

**Bỏ thích bình luận**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID bình luận |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đã bỏ thích bình luận",
  "data": {
    "luot_thich": 5
  }
}
```

---

## Media

### POST /media/upload

**Upload file (ảnh/video)**

**Authentication:** Required

**Request Body (Multipart/Form-Data):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `file` | file | ✅ | File ảnh (jpg, png, webp) hoặc video (mp4) |
| `loai_file` | string | ❌ | "image" hoặc "video" (auto-detect nếu không gửi) |

**Limits:**
- Citizen: 10MB
- Agency: 20MB
- Admin: 50MB

**Response 201 Created:**
```json
{
  "success": true,
  "message": "Upload thành công",
  "data": {
    "id": 123,
    "url": "https://media.cityresq360.io.vn/...",
    "thumbnail_url": "https://media.cityresq360.io.vn/...",
    "loai_file": "image",
    "kich_thuoc": 1024000,
    "created_at": "..."
  }
}
```

---

### GET /media/my

**Lấy danh sách media của tôi**

**Authentication:** Required

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | ❌ | Trang hiện tại |
| `loai_file` | string | ❌ | Filter: "image" hoặc "video" |

**Response 200 OK:**
```json
{
  "success": true,
  "data": [...]
}
```

---

### GET /media/{id}

**Lấy chi tiết media**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID media |

**Response 200 OK:**
```json
{
  "success": true,
  "data": {...}
}
```

---

### DELETE /media/{id}

**Xóa media (chỉ owner)**

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID media |

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Xóa media thành công"
}
```

---

## Map & Location

### GET /map/reports

**Lấy danh sách phản ánh hiển thị trên bản đồ (GeoJSON)**

**Authentication:** Optional

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `min_lat` | float | ❌ | Vĩ độ tối thiểu (viewport) |
| `max_lat` | float | ❌ | Vĩ độ tối đa |
| `min_lon` | float | ❌ | Kinh độ tối thiểu |
| `max_lon` | float | ❌ | Kinh độ tối đa |
| `danh_muc` | integer | ❌ | Filter danh mục |
| `trang_thai` | integer | ❌ | Filter trạng thái |

**Response 200 OK:**
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": {
        "type": "Point",
        "coordinates": [106.7009, 10.7769]
      },
      "properties": {
        "id": 1,
        "title": "...",
        "category_id": 1,
        "status": 0,
        "priority_level": 1,
        "marker_color": "#FFA500"
      }
    }
  ]
}
```

---

### GET /map/heatmap

**Lấy dữ liệu heatmap (mật độ phản ánh)**

**Authentication:** Optional

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    [10.7769, 106.7009, 5], // [lat, lon, intensity]
    ...
  ]
}
```

---

### GET /map/clusters

**Lấy dữ liệu gom nhóm (clusters)**

**Authentication:** Optional

**Query Parameters:**
- `zoom`: Mức zoom bản đồ (1-20)

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "lat": 10.77,
      "lon": 106.70,
      "count": 15,
      "id": "cluster_1"
    }
  ]
}
```

---

### GET /map/gtfs-routes

**Lấy lộ trình phương tiện công cộng (GTFS)**

**Authentication:** Optional

**Response 200 OK:**
```json
{
  "success": true,
  "data": [...]
}
```

---

## Agencies

### GET /agencies

**Lấy danh sách cơ quan chức năng**

**Authentication:** Optional

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "ten_co_quan": "Sở Giao thông Vận tải",
      "so_dien_thoai": "...",
      "dia_chi": "..."
    }
  ]
}
```

---

### GET /agencies/{id}

**Lấy chi tiết cơ quan**

**Authentication:** Optional

**Response 200 OK:**
```json
{
  "success": true,
  "data": {...}
}
```

---

### GET /agencies/{id}/reports

**Lấy danh sách phản ánh do cơ quan xử lý**

**Authentication:** Optional

**Response 200 OK:**
```json
{
  "success": true,
  "data": [...]
}
```

---

### GET /agencies/{id}/stats

**Lấy thống kê xử lý của cơ quan**

**Authentication:** Optional

**Response 200 OK:**
```json
{
  "success": true,
  "data": {
    "total_assigned": 100,
    "resolved": 80,
    "pending": 20,
    "avg_resolution_time": "2 days"
  }
}
```

---
## User Profile & Stats

### GET /users/stats

**Lấy thống kê cá nhân**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "data": {
    "total_reports": 15,
    "total_upvotes_received": 50,
    "city_points": 150,
    "rank": "Citizen Hero",
    "rank_progress": 75 // % to next rank
  }
}
```

---

### GET /users/leaderboard

**Lấy bảng xếp hạng người dùng tích cực**

**Authentication:** Optional

**Query Parameters:**
- `period`: "week", "month", "all" (default: "month")

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "rank": 1,
      "user": {...},
      "points": 500
    },
    ...
  ]
}
```

---

### GET /users/activities

**Lấy lịch sử hoạt động của tôi**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "type": "create_report",
      "description": "Đã tạo phản ánh...",
      "created_at": "..."
    }
  ]
}
```

---

## Wallet & CityPoints

### GET /wallet

**Lấy thông tin ví và số dư CityPoints**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "data": {
    "balance": 150,
    "currency": "CityPoint",
    "wallet_address": "0x..."
  }
}
```

---

### GET /wallet/transactions

**Lấy lịch sử giao dịch**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "reward",
      "amount": 10,
      "description": "Thưởng tạo phản ánh",
      "created_at": "..."
    }
  ]
}
```

---

### POST /wallet/deposit

**Nạp điểm (Simulated - Dev only)**

**Authentication:** Required

**Request Body:**
- `amount`: integer

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Nạp điểm thành công"
}
```

---

### POST /wallet/withdraw

**Rút điểm/Đổi quà (Simulated)**

**Authentication:** Required

**Request Body:**
- `amount`: integer

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đổi quà thành công"
}
```

---

## Notifications

### GET /notifications

**Lấy danh sách thông báo**

**Authentication:** Required

**Query Parameters:**
- `page`: integer
- `unread_only`: boolean (true/false)

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "type": "report_status_updated",
      "title": "Phản ánh đã được xử lý",
      "body": "Phản ánh 'Ổ gà...' của bạn đã được xử lý xong.",
      "data": { "report_id": 123 },
      "read_at": null,
      "created_at": "..."
    }
  ]
}
```

---

### POST /notifications/{id}/read

**Đánh dấu thông báo đã đọc**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đã đánh dấu đã đọc"
}
```

---

### POST /notifications/read-all

**Đánh dấu tất cả là đã đọc**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Đã đánh dấu tất cả là đã đọc"
}
```

---

### DELETE /notifications/{id}

**Xóa thông báo**

**Authentication:** Required

**Response 200 OK:**
```json
{
  "success": true,
  "message": "Xóa thông báo thành công"
}
```

---

## Categories & Priorities

### GET /categories

**Lấy danh sách danh mục phản ánh**

**Authentication:** Optional

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "ten_danh_muc": "Giao thông",
      "mo_ta": "Tai nạn, ùn tắc, hư hỏng đường bộ...",
      "icon_url": "..."
    },
    ...
  ]
}
```

---

### GET /priorities

**Lấy danh sách mức độ ưu tiên**

**Authentication:** Optional

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "level": 0,
      "ten_muc": "Thấp",
      "mau_sac": "#00FF00",
      "thoi_gian_xu_ly": "7 ngày"
    },
    {
      "id": 2,
      "level": 1,
      "ten_muc": "Trung bình",
      "mau_sac": "#FFA500",
      "thoi_gian_xu_ly": "3 ngày"
    },
    ...
  ]
}
```

---

## Weather Data

### GET /weather/current

**Lấy thông tin thời tiết hiện tại**

**Authentication:** Optional

**Query Parameters:**
- `lat`: float
- `lon`: float

**Response 200 OK:**
```json
{
  "success": true,
  "data": {
    "temp": 30,
    "condition": "Sunny",
    "humidity": 70,
    "wind_speed": 5,
    "aqi": 45
  }
}
```

---

### GET /weather/forecast

**Lấy dự báo thời tiết**

**Authentication:** Optional

**Query Parameters:**
- `lat`: float
- `lon`: float
- `days`: integer (default: 3)

**Response 200 OK:**
```json
{
  "success": true,
  "data": [...]
}
```

---

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| `400` | Bad Request | Request không hợp lệ |
| `401` | Unauthorized | Chưa đăng nhập hoặc token hết hạn |
| `403` | Forbidden | Không có quyền thực hiện hành động |
| `404` | Not Found | Resource không tồn tại |
| `422` | Unprocessable Entity | Dữ liệu gửi lên không hợp lệ (Validation error) |
| `429` | Too Many Requests | Gửi quá nhiều request (Rate limit) |
| `500` | Internal Server Error | Lỗi server |
| `502` | Bad Gateway | Lỗi kết nối giữa các services |
| `503` | Service Unavailable | Service đang bảo trì |

---
**End of Documentation**
