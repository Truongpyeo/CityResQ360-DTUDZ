# 📱 CLIENT API - LỘ TRÌNH TRIỂN KHAI

> Hướng dẫn chi tiết triển khai Client API (Mobile App) cho CityResQ360

**Ngày bắt đầu:** November 22, 2025  
**Thời gian dự kiến:** 10-14 ngày  
**Status:** ⏳ 45% - In Progress (Infrastructure + Core APIs Complete)  
**Tech Stack:** Laravel 12 + Sanctum + REST API

---

## 📋 MỤC LỤC

1. [Tổng quan](#1-tổng-quan)
2. [Kiến trúc Client API](#2-kiến-trúc-client-api)
3. [Authentication & Authorization](#3-authentication--authorization)
4. [API Endpoints](#4-api-endpoints)
5. [Lộ trình chi tiết](#5-lộ-trình-chi-tiết)
6. [Implementation Guide](#6-implementation-guide)

---

## **1. TỔNG QUAN**

### 🎯 **Mục tiêu**

Xây dựng RESTful API hoàn chỉnh cho Mobile App (React Native) với các chức năng:
- ✅ Authentication (Register, Login, JWT Tokens)
- ✅ User Profile Management
- ✅ Reports CRUD (Phản ánh sự cố)
- ✅ Comments & Voting
- ✅ Media Upload
- ✅ Map & Location Services
- ✅ Wallet & CityPoints
- ✅ Notifications
- ✅ Real-time Updates

### 📊 **Tech Stack**

- **API Style:** RESTful JSON API
- **Authentication:** Laravel Sanctum (Token-based)
- **Validation:** Form Requests
- **Response Format:** JSON with consistent structure
- **Rate Limiting:** Per user/endpoint
- **Versioning:** URL-based (/api/v1)

### 👥 **User Roles (Client)**

| Role | Mô tả |
|------|-------|
| **Citizen (0)** | Người dân thường - tạo phản ánh, bình luận, vote |
| **Officer (1)** | Cán bộ - quyền cao hơn, có thể xác nhận phản ánh |

---

## **2. KIẾN TRÚC CLIENT API**

### 🏗️ **Folder Structure**

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── BaseController.php           # Base API controller
│   │       └── V1/
│   │           ├── AuthController.php       # Authentication
│   │           ├── UserController.php       # User profile
│   │           ├── ReportController.php     # Reports CRUD
│   │           ├── CommentController.php    # Comments
│   │           ├── VoteController.php       # Voting system
│   │           ├── MediaController.php      # File upload
│   │           ├── MapController.php        # Map & location
│   │           ├── WalletController.php     # CityPoints
│   │           ├── NotificationController.php # Notifications
│   │           └── AgencyController.php     # View agencies (read-only)
│   ├── Middleware/
│   │   ├── ApiAuthenticate.php              # API auth middleware
│   │   └── ApiRateLimiting.php              # Rate limiting
│   ├── Requests/
│   │   └── Api/
│   │       ├── Auth/
│   │       │   ├── RegisterRequest.php
│   │       │   ├── LoginRequest.php
│   │       │   ├── UpdateProfileRequest.php
│   │       │   └── ChangePasswordRequest.php
│   │       ├── Report/
│   │       │   ├── StoreReportRequest.php
│   │       │   ├── UpdateReportRequest.php
│   │       │   └── RateReportRequest.php
│   │       ├── Comment/
│   │       │   ├── StoreCommentRequest.php
│   │       │   └── UpdateCommentRequest.php
│   │       └── Media/
│   │           └── UploadMediaRequest.php
│   └── Resources/
│       └── Api/
│           ├── UserResource.php
│           ├── ReportResource.php
│           ├── ReportCollection.php
│           ├── CommentResource.php
│           ├── AgencyResource.php
│           ├── NotificationResource.php
│           └── WalletResource.php
├── Services/
│   ├── AIClassificationService.php          # AI classification
│   ├── EventPublishService.php              # Event publishing
│   ├── MediaService.php                     # Media processing
│   ├── NotificationService.php              # Push notifications
│   ├── WalletService.php                    # CityPoints logic
│   └── LocationService.php                  # Geospatial queries
├── Events/
│   ├── ReportCreated.php
│   ├── ReportUpdated.php
│   ├── CommentCreated.php
│   └── VoteRecorded.php
└── Helpers/
    └── ApiResponse.php                      # Response helpers

routes/
└── api.php                                  # API routes

config/
└── sanctum.php                              # Sanctum config
```

---

## **3. AUTHENTICATION & AUTHORIZATION**

### 🔐 **Laravel Sanctum Setup**

**Authentication Flow:**
```
1. User registers → Email verification (optional)
2. User logins → Receive Bearer Token
3. Include token in headers: Authorization: Bearer {token}
4. Logout → Revoke token
```

**Token Configuration:**
```php
// config/sanctum.php
'expiration' => 60 * 24 * 30, // 30 days
'token_prefix' => 'cityresq_',
```

**Middleware:**
```php
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes
});
```

---

## **4. API ENDPOINTS**

### 📍 **Base URL:** `/api/v1`

---

### 🔐 **4.1. AUTHENTICATION MODULE**

#### **Public Routes (No Auth Required)**

```php
// Register new account
POST   /api/v1/auth/register
Body: {
  "ho_ten": "Nguyễn Văn A",
  "email": "nguyenvana@example.com",
  "mat_khau": "password123",
  "mat_khau_confirmation": "password123",
  "so_dien_thoai": "0901234567"
}
Response: {
  "success": true,
  "message": "Đăng ký thành công",
  "data": {
    "user": {...},
    "token": "1|abc123..."
  }
}

// Login
POST   /api/v1/auth/login
Body: {
  "email": "nguyenvana@example.com",
  "mat_khau": "password123",
  "remember": true
}
Response: {
  "success": true,
  "message": "Đăng nhập thành công",
  "data": {
    "user": {...},
    "token": "2|xyz789..."
  }
}

// Forgot password
POST   /api/v1/auth/forgot-password
Body: {
  "email": "nguyenvana@example.com"
}

// Reset password
POST   /api/v1/auth/reset-password
Body: {
  "email": "nguyenvana@example.com",
  "token": "reset_token",
  "mat_khau": "newpassword123",
  "mat_khau_confirmation": "newpassword123"
}
```

#### **Protected Routes (Auth Required)**

```php
// Get current user
GET    /api/v1/auth/me
Headers: Authorization: Bearer {token}
Response: {
  "success": true,
  "data": {
    "id": 1,
    "ho_ten": "Nguyễn Văn A",
    "email": "nguyenvana@example.com",
    "so_dien_thoai": "0901234567",
    "anh_dai_dien": "https://...",
    "vai_tro": 0,
    "diem_thanh_pho": 150,
    "diem_uy_tin": 85,
    "cap_huy_hieu": 1,
    "xac_thuc_cong_dan": true,
    "tong_so_phan_anh": 12,
    "ty_le_chinh_xac": 91.67
  }
}

// Update profile
PUT    /api/v1/auth/profile
Body: {
  "ho_ten": "Nguyễn Văn A Updated",
  "so_dien_thoai": "0909999999",
  "anh_dai_dien": "base64_image_or_url"
}

// Change password
POST   /api/v1/auth/change-password
Body: {
  "mat_khau_cu": "oldpassword",
  "mat_khau_moi": "newpassword123",
  "mat_khau_moi_confirmation": "newpassword123"
}

// Logout
POST   /api/v1/auth/logout
Response: {
  "success": true,
  "message": "Đăng xuất thành công"
}

// Refresh token
POST   /api/v1/auth/refresh
Response: {
  "success": true,
  "data": {
    "token": "3|newtoken..."
  }
}

// Verify email
POST   /api/v1/auth/verify-email
Body: {
  "code": "123456"
}

// Verify phone
POST   /api/v1/auth/verify-phone
Body: {
  "code": "123456"
}

// Update FCM token (for push notifications)
POST   /api/v1/auth/update-fcm-token
Body: {
  "push_token": "fcm_device_token_here"
}
```

---

### 📝 **4.2. REPORTS MODULE (Phản ánh)**

```php
// List reports with filters
GET    /api/v1/reports
Query: ?page=1&per_page=15&danh_muc=0&trang_thai=0&uu_tien=2&sort_by=ngay_tao&sort_order=desc
Response: {
  "success": true,
  "data": [
    {
      "id": 123,
      "tieu_de": "Đường bị ổ gà",
      "mo_ta": "...",
      "danh_muc": 0,
      "danh_muc_text": "Giao thông",
      "trang_thai": 1,
      "trang_thai_text": "Đã xác nhận",
      "uu_tien": 2,
      "uu_tien_text": "Cao",
      "vi_do": 10.8231,
      "kinh_do": 106.6297,
      "dia_chi": "123 Nguyễn Huệ, Q1, HCM",
      "luot_ung_ho": 15,
      "luot_khong_ung_ho": 2,
      "luot_xem": 234,
      "nhan_ai": "Hư hỏng đường bộ",
      "do_tin_cay": 0.92,
      "user": {
        "id": 1,
        "ho_ten": "Nguyễn Văn A",
        "anh_dai_dien": "..."
      },
      "agency": {
        "id": 5,
        "ten_co_quan": "UBND Quận 1"
      },
      "media": [
        {
          "id": 456,
          "url": "https://...",
          "type": "image"
        }
      ],
      "ngay_tao": "2025-11-20T10:30:00Z",
      "ngay_cap_nhat": "2025-11-20T15:45:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 234,
    "last_page": 16
  }
}

// Create new report
POST   /api/v1/reports
Body: {
  "tieu_de": "Đường Nguyễn Huệ bị ngập nặng",
  "mo_ta": "Sau cơn mưa sáng nay...",
  "danh_muc": 4,
  "uu_tien": 2,
  "vi_do": 10.8231,
  "kinh_do": 106.6297,
  "dia_chi": "Đường Nguyễn Huệ, Quận 1, HCM",
  "la_cong_khai": true,
  "the_tags": ["ngập lụt", "giao thông", "khẩn cấp"],
  "media_ids": [123, 456]
}
Response: {
  "success": true,
  "message": "Tạo phản ánh thành công. Bạn nhận được +10 CityPoints!",
  "data": {
    "id": 12345,
    "tieu_de": "...",
    "nhan_ai": "Ngập lụt đô thị",
    "do_tin_cay": 0.89,
    ...
  }
}

// Get report detail
GET    /api/v1/reports/{id}
Response: {
  "success": true,
  "data": {
    "id": 123,
    "tieu_de": "...",
    "mo_ta": "...",
    "user": {...},
    "agency": {...},
    "media": [...],
    "comments": [
      {
        "id": 1,
        "noi_dung": "Tôi cũng gặp vấn đề tương tự",
        "user": {...},
        "ngay_tao": "..."
      }
    ],
    "votes": {
      "total_upvotes": 15,
      "total_downvotes": 2,
      "user_voted": 1 // 1: upvoted, -1: downvoted, null: not voted
    }
  }
}

// Update report (only author or admin)
PUT    /api/v1/reports/{id}
Body: {
  "tieu_de": "Updated title",
  "mo_ta": "Updated description",
  "uu_tien": 3
}

// Delete report (only author or admin)
DELETE /api/v1/reports/{id}

// Get my reports
GET    /api/v1/reports/my
Query: ?page=1&trang_thai=0

// Get nearby reports (location-based)
GET    /api/v1/reports/nearby
Query: ?vi_do=10.8231&kinh_do=106.6297&radius=5
// radius in kilometers

// Get trending reports (most upvotes)
GET    /api/v1/reports/trending
Query: ?page=1&limit=10

// Vote report (upvote/downvote)
POST   /api/v1/reports/{id}/vote
Body: {
  "loai_binh_chon": 1  // 1: upvote, -1: downvote
}
Response: {
  "success": true,
  "message": "Vote thành công",
  "data": {
    "luot_ung_ho": 16,
    "luot_khong_ung_ho": 2,
    "user_voted": 1
  }
}

// Increment view count
POST   /api/v1/reports/{id}/view

// Rate report (after resolved)
POST   /api/v1/reports/{id}/rate
Body: {
  "danh_gia_hai_long": 5,  // 1-5 stars
  "nhan_xet": "Xử lý rất nhanh và hiệu quả!"
}
```

---

### 💬 **4.3. COMMENTS MODULE**

```php
// List comments for a report
GET    /api/v1/reports/{id}/comments
Query: ?page=1&sort_by=ngay_tao&sort_order=desc
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "noi_dung": "Tôi cũng gặp vấn đề tương tự",
      "user": {
        "id": 2,
        "ho_ten": "Trần Thị B",
        "anh_dai_dien": "..."
      },
      "luot_thich": 5,
      "user_liked": false,
      "ngay_tao": "2025-11-20T11:30:00Z"
    }
  ]
}

// Add comment
POST   /api/v1/reports/{id}/comments
Body: {
  "noi_dung": "Tôi cũng gặp vấn đề tương tự ở đoạn đường này"
}

// Update comment (only author)
PUT    /api/v1/comments/{id}
Body: {
  "noi_dung": "Updated comment content"
}

// Delete comment (only author or admin)
DELETE /api/v1/comments/{id}

// Like comment
POST   /api/v1/comments/{id}/like
Response: {
  "success": true,
  "data": {
    "luot_thich": 6,
    "user_liked": true
  }
}

// Unlike comment
DELETE /api/v1/comments/{id}/like
```

---

### 📷 **4.4. MEDIA MODULE**

```php
// Upload media (image/video)
POST   /api/v1/media/upload
Headers: Content-Type: multipart/form-data
Body: {
  "file": <binary>,
  "type": "image", // image, video
  "lien_ket_den": "phan_anh", // phan_anh, binh_luan
  "mo_ta": "Hình ảnh hiện trường"
}
Response: {
  "success": true,
  "message": "Upload thành công",
  "data": {
    "id": 789,
    "url": "https://storage.cityresq360.com/media/abc123.jpg",
    "thumbnail_url": "https://storage.cityresq360.com/media/thumb_abc123.jpg",
    "type": "image",
    "kich_thuoc": 2048576,
    "dinh_dang": "image/jpeg"
  }
}

// Get media detail
GET    /api/v1/media/{id}

// Delete media (only owner)
DELETE /api/v1/media/{id}

// List user's uploaded media
GET    /api/v1/media/my
Query: ?page=1&type=image
```

---

### 🗺️ **4.5. MAP MODULE**

```php
// Get all reports for map display
GET    /api/v1/map/reports
Query: ?bounds=10.7,106.6,10.9,106.8&danh_muc=0,1,4&trang_thai=0,1,2
// bounds: min_lat,min_lon,max_lat,max_lon
Response: {
  "success": true,
  "data": [
    {
      "id": 123,
      "vi_do": 10.8231,
      "kinh_do": 106.6297,
      "tieu_de": "Đường bị ổ gà",
      "danh_muc": 0,
      "uu_tien": 2,
      "trang_thai": 1,
      "marker_color": "#FF5733"
    }
  ]
}

// Get heatmap data
GET    /api/v1/map/heatmap
Query: ?bounds=...&danh_muc=0,1,4&tu_ngay=2025-11-01&den_ngay=2025-11-30
Response: {
  "success": true,
  "data": [
    {
      "vi_do": 10.8231,
      "kinh_do": 106.6297,
      "weight": 5
    }
  ]
}

// Get cluster markers
GET    /api/v1/map/clusters
Query: ?zoom=12&bounds=...

// Get GTFS routes (public transport)
GET    /api/v1/map/routes
Query: ?vi_do=10.8231&kinh_do=106.6297&radius=2
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "ten_tuyen": "Tuyến xe buýt số 1",
      "diem_dung": [
        {
          "id": 1,
          "ten_diem": "Bến xe buýt Bến Thành",
          "vi_do": 10.8231,
          "kinh_do": 106.6297
        }
      ]
    }
  ]
}
```

---

### 💰 **4.6. WALLET MODULE (CityPoints)**

```php
// Get wallet balance
GET    /api/v1/wallet
Response: {
  "success": true,
  "data": {
    "diem_thanh_pho": 350,
    "diem_uy_tin": 85,
    "cap_huy_hieu": 1,
    "cap_huy_hieu_text": "Bạc",
    "next_level_points": 500,
    "progress_percentage": 70
  }
}

// Get transaction history
GET    /api/v1/wallet/transactions
Query: ?page=1&loai_giao_dich=0
// loai_giao_dich: 0=reward, 1=spend, 2=admin_adjust
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "loai_giao_dich": 0,
      "loai_giao_dich_text": "Thưởng",
      "so_diem": 10,
      "so_du_truoc": 340,
      "so_du_sau": 350,
      "ly_do": "Tạo phản ánh chính xác",
      "ngay_tao": "2025-11-20T10:30:00Z"
    }
  ]
}

// Redeem points (spend)
POST   /api/v1/wallet/redeem
Body: {
  "phan_thuong_id": 5,
  "so_diem": 100
}
Response: {
  "success": true,
  "message": "Đổi điểm thành công!",
  "data": {
    "so_du_moi": 250,
    "voucher_code": "CITY2025ABC"
  }
}

// Get available rewards catalog
GET    /api/v1/wallet/rewards
Query: ?page=1&loai=0
// loai: 0=voucher, 1=gift, 2=service
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "ten_phan_thuong": "Voucher Grab 50k",
      "mo_ta": "Giảm 50.000đ cho chuyến đi Grab",
      "so_diem_can": 100,
      "hinh_anh": "https://...",
      "so_luong_con_lai": 50,
      "ngay_het_han": "2025-12-31"
    }
  ]
}
```

---

### 🔔 **4.7. NOTIFICATIONS MODULE**

```php
// List notifications
GET    /api/v1/notifications
Query: ?page=1&da_doc=false
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "tieu_de": "Phản ánh của bạn đã được xác nhận",
      "noi_dung": "Phản ánh #123 'Đường bị ổ gà' đã được UBND Quận 1 xác nhận và đang xử lý",
      "loai": "report_status_update",
      "da_doc": false,
      "du_lieu_mo_rong": {
        "phan_anh_id": 123,
        "trang_thai_moi": 2
      },
      "ngay_tao": "2025-11-20T15:30:00Z"
    }
  ],
  "meta": {
    "unread_count": 5
  }
}

// Get unread notifications count
GET    /api/v1/notifications/unread-count
Response: {
  "success": true,
  "data": {
    "count": 5
  }
}

// Get unread notifications
GET    /api/v1/notifications/unread

// Mark notification as read
POST   /api/v1/notifications/{id}/read

// Mark all as read
POST   /api/v1/notifications/read-all

// Delete notification
DELETE /api/v1/notifications/{id}

// Update notification settings
PUT    /api/v1/notifications/settings
Body: {
  "email_enabled": true,
  "push_enabled": true,
  "report_status_update": true,
  "report_assigned": true,
  "comment_reply": true,
  "system_announcement": true
}
```

---

### 📊 **4.8. DASHBOARD/STATS MODULE**

```php
// Get user's overview statistics
GET    /api/v1/stats/overview
Response: {
  "success": true,
  "data": {
    "tong_so_phan_anh": 12,
    "cho_xu_ly": 3,
    "dang_xu_ly": 4,
    "da_giai_quyet": 5,
    "tu_choi": 0,
    "ty_le_chinh_xac": 91.67,
    "diem_uy_tin": 85,
    "xep_hang": 45
  }
}

// Get reports by category (user's reports)
GET    /api/v1/stats/categories
Response: {
  "success": true,
  "data": [
    {
      "danh_muc": 0,
      "danh_muc_text": "Giao thông",
      "total": 5
    },
    {
      "danh_muc": 4,
      "danh_muc_text": "Ngập lụt",
      "total": 3
    }
  ]
}

// Get timeline chart data (user's reports over time)
GET    /api/v1/stats/timeline
Query: ?tu_ngay=2025-01-01&den_ngay=2025-11-30

// Get leaderboard (top users by reputation)
GET    /api/v1/stats/leaderboard
Query: ?page=1&limit=50
Response: {
  "success": true,
  "data": [
    {
      "rank": 1,
      "user": {
        "id": 5,
        "ho_ten": "Nguyễn Văn X",
        "anh_dai_dien": "...",
        "cap_huy_hieu": 3
      },
      "diem_uy_tin": 980,
      "tong_so_phan_anh": 45,
      "ty_le_chinh_xac": 95.6
    }
  ]
}

// Get city-wide statistics (public)
GET    /api/v1/stats/city
Response: {
  "success": true,
  "data": {
    "tong_phan_anh": 2456,
    "da_giai_quyet": 1890,
    "dang_xu_ly": 345,
    "ty_le_giai_quyet": 76.96,
    "thoi_gian_xu_ly_trung_binh": 72, // hours
    "top_danh_muc": [
      {
        "danh_muc": 0,
        "danh_muc_text": "Giao thông",
        "total": 856
      }
    ]
  }
}
```

---

### 🏢 **4.9. AGENCIES MODULE (Read-only for clients)**

```php
// List all agencies
GET    /api/v1/agencies
Query: ?page=1&cap_do=0
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "ten_co_quan": "UBND Quận 1",
      "email_lien_he": "ubndq1@hcm.gov.vn",
      "so_dien_thoai": "0283822xxxx",
      "dia_chi": "...",
      "cap_do": 1,
      "cap_do_text": "Quận/Huyện",
      "trang_thai": 1
    }
  ]
}

// Get agency detail
GET    /api/v1/agencies/{id}
Response: {
  "success": true,
  "data": {
    "id": 1,
    "ten_co_quan": "UBND Quận 1",
    "email_lien_he": "ubndq1@hcm.gov.vn",
    "so_dien_thoai": "0283822xxxx",
    "dia_chi": "...",
    "cap_do": 1,
    "cap_do_text": "Quận/Huyện",
    "mo_ta": "...",
    "trang_thai": 1
  }
}

// Get agency's public reports
GET    /api/v1/agencies/{id}/reports
Query: ?page=1&trang_thai=3

// Get agency statistics (public)
GET    /api/v1/agencies/{id}/stats
Response: {
  "success": true,
  "data": {
    "tong_phan_anh": 234,
    "da_giai_quyet": 189,
    "dang_xu_ly": 45,
    "ty_le_giai_quyet": 80.77,
    "thoi_gian_phan_hoi_trung_binh": 45, // minutes
    "thoi_gian_giai_quyet_trung_binh": 72 // hours
  }
}
```

---

### 👤 **4.10. USER PROFILE MODULE**

```php
// Get user profile (public view)
GET    /api/v1/users/{id}
Response: {
  "success": true,
  "data": {
    "id": 1,
    "ho_ten": "Nguyễn Văn A",
    "anh_dai_dien": "https://...",
    "cap_huy_hieu": 1,
    "cap_huy_hieu_text": "Bạc",
    "diem_uy_tin": 85,
    "tong_so_phan_anh": 12,
    "ty_le_chinh_xac": 91.67,
    "ngay_tham_gia": "2024-01-15T00:00:00Z"
  }
}

// Get user's public reports
GET    /api/v1/users/{id}/reports
Query: ?page=1

// Get user's statistics
GET    /api/v1/users/{id}/stats
```

---

## **5. LỘ TRÌNH CHI TIẾT**

### **📅 WEEK 1: Foundation & Core APIs (Day 1-7)**

#### **Day 1-2: API Infrastructure Setup** ⏳
```bash
✅ Configure routes/api.php với API versioning
✅ Create ApiResponse helper class
✅ Create BaseController for API
✅ Setup Sanctum configuration
✅ Configure CORS for mobile app
✅ Setup API rate limiting
✅ Create API middleware (auth, rate limit)
```

**Files to create:**
- `app/Helpers/ApiResponse.php`
- `app/Http/Controllers/Api/BaseController.php`
- `app/Http/Middleware/ApiAuthenticate.php`
- `routes/api.php` (complete structure)

---

#### **Day 3-4: Authentication API** ⏳
```bash
✅ AuthController with all methods
✅ RegisterRequest validation
✅ LoginRequest validation
✅ UpdateProfileRequest validation
✅ UserResource for API responses
✅ Sanctum token generation
✅ FCM token management
```

**Endpoints:**
- Register, Login, Logout
- Profile management
- Password reset flow
- Email/Phone verification
- FCM token update

**Files:**
- `app/Http/Controllers/Api/V1/AuthController.php`
- `app/Http/Requests/Api/Auth/*.php` (6 files)
- `app/Http/Resources/Api/UserResource.php`

---

#### **Day 5-7: Reports API (Core CRUD)** ⏳
```bash
✅ ReportController with CRUD operations
✅ StoreReportRequest validation
✅ ReportResource & ReportCollection
✅ AI Classification integration (mock)
✅ Event publishing (ReportCreated)
✅ Location-based queries
✅ Pagination & filtering
```

**Endpoints:**
- List reports (with filters)
- Create report
- Update report
- Delete report
- My reports
- Nearby reports
- View increment

**Files:**
- `app/Http/Controllers/Api/V1/ReportController.php`
- `app/Http/Requests/Api/Report/*.php` (3 files)
- `app/Http/Resources/Api/ReportResource.php`
- `app/Services/AIClassificationService.php` (stub)

---

### **📅 WEEK 2: Advanced Features (Day 8-14)**

#### **Day 8-9: Comments & Voting** ⏳
```bash
✅ CommentController CRUD
✅ VoteController (upvote/downvote)
✅ Comment likes
✅ Nested comments (optional)
✅ Real-time comment count update
```

**Files:**
- `app/Http/Controllers/Api/V1/CommentController.php`
- `app/Http/Controllers/Api/V1/VoteController.php`
- `app/Http/Resources/Api/CommentResource.php`

---

#### **Day 10: Media Upload** ⏳
```bash
✅ MediaController upload endpoint
✅ Image optimization
✅ Thumbnail generation
✅ Video processing (basic)
✅ Storage integration (MinIO/S3)
```

**Files:**
- `app/Http/Controllers/Api/V1/MediaController.php`
- `app/Services/MediaService.php`

---

#### **Day 11: Map & Location Services** ⏳
```bash
✅ MapController endpoints
✅ Geospatial queries (nearby)
✅ Heatmap data generation
✅ Cluster markers
✅ GTFS routes integration
```

**Files:**
- `app/Http/Controllers/Api/V1/MapController.php`
- `app/Services/LocationService.php`

---

#### **Day 12: Wallet & CityPoints** ⏳
```bash
✅ WalletController
✅ Transaction history
✅ Redeem points
✅ Rewards catalog
✅ Points calculation logic
```

**Files:**
- `app/Http/Controllers/Api/V1/WalletController.php`
- `app/Services/WalletService.php`

---

#### **Day 13: Notifications** ⏳
```bash
✅ NotificationController
✅ FCM push notifications
✅ Notification settings
✅ Read/unread management
```

**Files:**
- `app/Http/Controllers/Api/V1/NotificationController.php`
- `app/Services/NotificationService.php`

---

#### **Day 14: Stats & Polish** ⏳
```bash
✅ Stats endpoints (overview, categories, timeline)
✅ Leaderboard
✅ City-wide statistics
✅ API documentation (Postman/OpenAPI)
✅ Testing & bug fixes
```

---

## **6. IMPLEMENTATION GUIDE**

### 🔧 **Step 1: API Response Helper**

**File: `app/Helpers/ApiResponse.php`**

```php
<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Success response
     */
    public static function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Error response
     */
    public static function error(string $message = 'Error', $errors = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Validation error response
     */
    public static function validationError($errors, string $message = 'Dữ liệu không hợp lệ'): JsonResponse
    {
        return self::error($message, $errors, 422);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Chưa xác thực'): JsonResponse
    {
        return self::error($message, null, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Không có quyền truy cập'): JsonResponse
    {
        return self::error($message, null, 403);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Không tìm thấy'): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * Paginated response
     */
    public static function paginated($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
```

---

### 🔧 **Step 2: Base API Controller**

**File: `app/Http/Controllers/Api/BaseController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * Success response
     */
    protected function success($data = null, string $message = 'Success', int $code = 200)
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * Error response
     */
    protected function error(string $message = 'Error', $errors = null, int $code = 400)
    {
        return ApiResponse::error($message, $errors, $code);
    }

    /**
     * Validation error
     */
    protected function validationError($errors, string $message = 'Dữ liệu không hợp lệ')
    {
        return ApiResponse::validationError($errors, $message);
    }

    /**
     * Unauthorized
     */
    protected function unauthorized(string $message = 'Chưa xác thực')
    {
        return ApiResponse::unauthorized($message);
    }

    /**
     * Forbidden
     */
    protected function forbidden(string $message = 'Không có quyền truy cập')
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * Not found
     */
    protected function notFound(string $message = 'Không tìm thấy')
    {
        return ApiResponse::notFound($message);
    }

    /**
     * Paginated response
     */
    protected function paginated($paginator, string $message = 'Success')
    {
        return ApiResponse::paginated($paginator, $message);
    }
}
```

---

### 🔧 **Step 3: API Routes Structure**

**File: `routes/api.php`**

```php
<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\VoteController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\MapController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\AgencyController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| RESTful API for CityResQ360 Mobile App
| Base URL: /api/v1
| Authentication: Laravel Sanctum (Bearer Token)
|
*/

Route::prefix('v1')->group(function () {
    
    // ==========================================
    // PUBLIC ROUTES (No Authentication)
    // ==========================================
    
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Public agencies (read-only)
    Route::get('agencies', [AgencyController::class, 'index']);
    Route::get('agencies/{id}', [AgencyController::class, 'show']);
    Route::get('agencies/{id}/reports', [AgencyController::class, 'reports']);
    Route::get('agencies/{id}/stats', [AgencyController::class, 'stats']);

    // Public user profiles
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::get('users/{id}/reports', [UserController::class, 'reports']);
    Route::get('users/{id}/stats', [UserController::class, 'stats']);

    // Public statistics
    Route::get('stats/city', [UserController::class, 'cityStats']);
    Route::get('stats/leaderboard', [UserController::class, 'leaderboard']);

    
    // ==========================================
    // PROTECTED ROUTES (Authentication Required)
    // ==========================================
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // ========== Authentication Management ==========
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::put('profile', [AuthController::class, 'updateProfile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::post('verify-email', [AuthController::class, 'verifyEmail']);
            Route::post('verify-phone', [AuthController::class, 'verifyPhone']);
            Route::post('update-fcm-token', [AuthController::class, 'updateFcmToken']);
        });

        // ========== Reports Management ==========
        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index']);
            Route::post('/', [ReportController::class, 'store']);
            Route::get('my', [ReportController::class, 'myReports']);
            Route::get('nearby', [ReportController::class, 'nearby']);
            Route::get('trending', [ReportController::class, 'trending']);
            Route::get('{id}', [ReportController::class, 'show']);
            Route::put('{id}', [ReportController::class, 'update']);
            Route::delete('{id}', [ReportController::class, 'destroy']);
            Route::post('{id}/vote', [VoteController::class, 'vote']);
            Route::post('{id}/view', [ReportController::class, 'incrementView']);
            Route::post('{id}/rate', [ReportController::class, 'rate']);
            
            // Comments on reports
            Route::get('{id}/comments', [CommentController::class, 'index']);
            Route::post('{id}/comments', [CommentController::class, 'store']);
        });

        // ========== Comments Management ==========
        Route::prefix('comments')->group(function () {
            Route::put('{id}', [CommentController::class, 'update']);
            Route::delete('{id}', [CommentController::class, 'destroy']);
            Route::post('{id}/like', [CommentController::class, 'like']);
            Route::delete('{id}/like', [CommentController::class, 'unlike']);
        });

        // ========== Media Management ==========
        Route::prefix('media')->group(function () {
            Route::post('upload', [MediaController::class, 'upload']);
            Route::get('my', [MediaController::class, 'myMedia']);
            Route::get('{id}', [MediaController::class, 'show']);
            Route::delete('{id}', [MediaController::class, 'destroy']);
        });

        // ========== Map & Location Services ==========
        Route::prefix('map')->group(function () {
            Route::get('reports', [MapController::class, 'reports']);
            Route::get('heatmap', [MapController::class, 'heatmap']);
            Route::get('clusters', [MapController::class, 'clusters']);
            Route::get('routes', [MapController::class, 'gtfsRoutes']);
        });

        // ========== Wallet & CityPoints ==========
        Route::prefix('wallet')->group(function () {
            Route::get('/', [WalletController::class, 'balance']);
            Route::get('transactions', [WalletController::class, 'transactions']);
            Route::post('redeem', [WalletController::class, 'redeem']);
            Route::get('rewards', [WalletController::class, 'rewards']);
        });

        // ========== Notifications ==========
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('unread', [NotificationController::class, 'unread']);
            Route::get('unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{id}', [NotificationController::class, 'destroy']);
            Route::put('settings', [NotificationController::class, 'updateSettings']);
        });

        // ========== User Statistics ==========
        Route::prefix('stats')->group(function () {
            Route::get('overview', [UserController::class, 'overview']);
            Route::get('categories', [UserController::class, 'categoriesStats']);
            Route::get('timeline', [UserController::class, 'timeline']);
        });
    });
});
```

---

## **7. BEST PRACTICES**

### ✅ **API Response Format**

**Success:**
```json
{
  "success": true,
  "message": "Success message",
  "data": {...}
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": {...}
}
```

**Paginated:**
```json
{
  "success": true,
  "message": "Success",
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 234,
    "last_page": 16
  }
}
```

### ✅ **HTTP Status Codes**

- `200` - OK (Success)
- `201` - Created (Resource created)
- `204` - No Content (Success, no data)
- `400` - Bad Request (Client error)
- `401` - Unauthorized (Not authenticated)
- `403` - Forbidden (No permission)
- `404` - Not Found (Resource not found)
- `422` - Unprocessable Entity (Validation error)
- `429` - Too Many Requests (Rate limit)
- `500` - Internal Server Error

### ✅ **Rate Limiting**

```php
// config/sanctum.php
'middleware' => [
    'throttle:api', // 60 requests per minute
],
```

### ✅ **Validation Messages (Vietnamese)**

```php
'required' => 'Trường :attribute là bắt buộc',
'email' => ':attribute phải là địa chỉ email hợp lệ',
'min' => ':attribute phải có ít nhất :min ký tự',
'max' => ':attribute không được vượt quá :max ký tự',
```

---

## **8. TESTING STRATEGY**

### 🧪 **API Testing**

```bash
# Feature tests
php artisan make:test Api/AuthTest
php artisan make:test Api/ReportTest
php artisan make:test Api/CommentTest

# Run tests
php artisan test --filter=Api
```

### 🧪 **Postman Collection**

- Export all endpoints to Postman
- Include example requests/responses
- Environment variables (base_url, token)

---

## **9. DEPLOYMENT CHECKLIST**

### ☑️ **Pre-deployment**

- [ ] All API endpoints tested
- [ ] Sanctum configured correctly
- [ ] CORS configured for mobile app
- [ ] Rate limiting enabled
- [ ] Validation complete
- [ ] Error handling consistent
- [ ] API documentation ready

### ☑️ **Security**

- [ ] Token expiration configured
- [ ] Input sanitization
- [ ] SQL injection prevention
- [ ] XSS protection
- [ ] HTTPS enforced
- [ ] Sensitive data encrypted

### ☑️ **Performance**

- [ ] Database queries optimized
- [ ] Eager loading configured
- [ ] Response caching
- [ ] Image optimization
- [ ] API response time < 200ms

---

## **📚 RESOURCES**

- [Laravel Sanctum Docs](https://laravel.com/docs/12.x/sanctum)
- [API Resource Docs](https://laravel.com/docs/12.x/eloquent-resources)
- [Validation Docs](https://laravel.com/docs/12.x/validation)
- [REST API Best Practices](https://restfulapi.net/)

---

## **📊 PROGRESS TRACKER**

### ✅ **Hoàn thành (45%)**

**Infrastructure (3/3) ✅:**
- [x] API routes structure (`routes/api.php`)
- [x] Helper classes (`app/Helpers/ApiResponse.php` - 11 methods)
- [x] Base controllers (`app/Http/Controllers/Api/BaseController.php`)

**Authentication (8/8) ✅:**
- [x] Register endpoint (POST `/api/v1/auth/register`)
- [x] Login endpoint (POST `/api/v1/auth/login`)
- [x] Logout endpoint (POST `/api/v1/auth/logout`)
- [x] Profile management (GET/PUT `/api/v1/auth/me`)
- [x] Password change (POST `/api/v1/auth/change-password`)
- [x] Password reset flow (forgot/reset)
- [x] Email/Phone verification (verify-email/verify-phone)
- [x] FCM token update (POST `/api/v1/auth/fcm-token`)

**Reports Module (11/11) ✅:**
- [x] List reports (GET `/api/v1/reports`)
- [x] Create report (POST `/api/v1/reports`)
- [x] Show report detail (GET `/api/v1/reports/{id}`)
- [x] Update report (PUT `/api/v1/reports/{id}`)
- [x] Delete report (DELETE `/api/v1/reports/{id}`)
- [x] My reports (GET `/api/v1/reports/my`)
- [x] Nearby reports (GET `/api/v1/reports/nearby` - Haversine formula)
- [x] Trending reports (GET `/api/v1/reports/trending`)
- [x] Vote system (POST `/api/v1/reports/{id}/vote` - smart toggle/change)
- [x] View tracking (POST `/api/v1/reports/{id}/view`)
- [x] Rate report (POST `/api/v1/reports/{id}/rate` - 1-5 stars)

**Comments (6/6) ✅:**
- [x] List comments (GET `/api/v1/reports/{id}/comments`)
- [x] Create comment (POST `/api/v1/reports/{id}/comments`)
- [x] Update comment (PUT `/api/v1/comments/{id}`)
- [x] Delete comment (DELETE `/api/v1/comments/{id}`)
- [x] Like comment (POST `/api/v1/comments/{id}/like`)
- [x] Unlike comment (DELETE `/api/v1/comments/{id}/unlike`)

**Media (4/4) ✅:**
- [x] Upload endpoint (POST `/api/v1/media/upload`)
- [x] Image optimization (Intervention Image)
- [x] Thumbnail generation (300x300)
- [x] My media list (GET `/api/v1/media/my`)

**Form Requests (16/16) ✅:**
- [x] Auth: RegisterRequest, LoginRequest, UpdateProfileRequest, ChangePasswordRequest, ForgotPasswordRequest, ResetPasswordRequest, VerifyCodeRequest, UpdateFcmTokenRequest
- [x] Report: StoreReportRequest, UpdateReportRequest, NearbyReportRequest, RateReportRequest
- [x] Vote: VoteRequest
- [x] Comment: StoreCommentRequest, UpdateCommentRequest
- [x] Media: UploadMediaRequest

### ⏳ **Đang thực hiện**

**Controllers Refactoring (5/5) ✅:**
- [x] AuthController - All 12 methods using Form Requests
- [x] ReportController - All validation methods using Form Requests
- [x] VoteController - Using VoteRequest
- [x] CommentController - Using StoreCommentRequest, UpdateCommentRequest
- [x] MediaController - Using UploadMediaRequest

### 🔴 **Chưa bắt đầu (55%)**

**API Resources (0/3):**
- [ ] UserResource
- [ ] ReportResource
- [ ] CommentResource

**Map Services (0/4):**
- [ ] Map reports
- [ ] Heatmap data
- [ ] Clusters
- [ ] GTFS routes

**Wallet (0/4):**
- [ ] Balance endpoint
- [ ] Transaction history
- [ ] Redeem points
- [ ] Rewards catalog

**Notifications (0/6):**
- [ ] List notifications
- [ ] Unread count
- [ ] Mark as read
- [ ] FCM integration
- [ ] Notification settings
- [ ] Real-time updates

**Statistics (0/4):**
- [ ] User overview
- [ ] Categories stats
- [ ] Timeline
- [ ] Leaderboard

---

---

## **⚠️ VẤN ĐỀ CẦN GIẢI QUYẾT**

### 🔴 **Critical Issues**

#### 1. **Model `HinhAnhPhanAnh` chưa tồn tại**
- **Vị trí:** `app/Models/HinhAnhPhanAnh.php`
- **Ảnh hưởng:** MediaController không hoạt động được
- **Cần tạo:**
  - Migration: `create_hinh_anh_phan_anhs_table`
  - Model với các trường:
    - `nguoi_dung_id` - User upload
    - `duong_dan_hinh_anh` - URL file gốc
    - `duong_dan_thumbnail` - URL thumbnail
    - `loai_file` - image/video
    - `kich_thuoc` - File size (bytes)
    - `dinh_dang` - MIME type
    - `mo_ta` - Description (nullable)
  - Relationships: `belongsTo(NguoiDung::class)`

#### 2. **Package Intervention Image chưa cài đặt**
- **Error:** `Undefined type 'Intervention\Image\Laravel\Facades\Image'`
- **Giải pháp:** 
  ```bash
  composer require intervention/image-laravel
  php artisan vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider"
  ```

#### 3. **Missing Relationships**
- `PhanAnh::binhChons()` - relationship chưa định nghĩa
- `PhanAnh::binhLuans()` - relationship có thể cần eager loading
- `BinhLuanPhanAnh::nguoiDung()` - relationship cần verify

### ⚠️ **Known Issues**

#### 4. **Email/SMS Verification chưa implement**
- `AuthController::forgotPassword()` - TODO: Email sending
- `AuthController::resetPassword()` - TODO: Token verification
- `AuthController::verifyEmail()` - TODO: Code verification logic
- `AuthController::verifyPhone()` - TODO: SMS integration

#### 5. **Comment Like System chưa hoàn chỉnh**
- `CommentController::index()` - `user_liked` luôn return `false`
- Cần table: `binh_luan_likes` hoặc tương tự
- Cần implement check like status

#### 6. **Media Upload - Storage Configuration**
- Cần verify `storage/app/public` đã link symbolic
- Cần test thumbnail generation với file thực
- Cần xử lý cleanup khi xóa report/comment có media

### 📝 **Code Quality Notes**

#### 7. **Form Request Pattern - Hoàn thành ✅**
- ✅ Tất cả controllers đã refactor từ `Validator::make()` sang Form Request
- ✅ Consistent error format: 422 với JSON response
- ✅ Vietnamese validation messages
- ✅ Dynamic rules (ví dụ: unique with user ID exclusion)

#### 8. **Authentication Pattern**
- ✅ Đã chuyển từ `auth()->check()` sang `$request->user() !== null`
- ✅ Đã chuyển từ `auth()->id()` sang `$request->user()->id`
- ✅ ReportController::show() đã fix privacy check

---

## **📅 NEXT STEPS**

### **Tuần này (Priority High):**

1. **Tạo Model & Migration cho HinhAnhPhanAnh** ⚠️
   - Migration với đầy đủ columns
   - Model với relationships
   - Factory & Seeder (optional)

2. **Cài đặt Intervention Image** ⚠️
   - `composer require intervention/image-laravel`
   - Config & test thumbnail generation

3. **Tạo API Resources** 📋
   - UserResource
   - ReportResource (với nested comments, votes)
   - CommentResource

4. **Fix Missing Relationships** ⚠️
   - `PhanAnh::binhChons()`
   - Verify eager loading

5. **Implement Comment Like System** 📋
   - Migration `binh_luan_likes`
   - Update CommentController logic

### **Tuần sau (Priority Medium):**

6. **Map Services Module**
   - MapController với heatmap, clusters
   - GTFS routes integration

7. **Wallet Module**
   - WalletController
   - CityPoints logic

8. **Notifications Module**
   - NotificationController
   - FCM integration testing

9. **Testing & Documentation**
   - Postman collection
   - API documentation
   - Integration tests

---

**Last Updated:** November 22, 2025  
**Version:** 1.0.0  
**Status:** ⏳ 45% - Infrastructure + Core APIs Complete  
**Next Milestone:** Fix Critical Issues + API Resources
