# 🚀 CoreAPI - API Development Guide

> Hướng dẫn chi tiết phát triển API cho 2 tác nhân: **Client (Mobile App)** và **Admin (Web Dashboard)**

---

## 📋 **MỤC LỤC**

1. [Tổng quan kiến trúc](#1-tổng-quan-kiến-trúc)
2. [Client API (Mobile App)](#2-client-api-mobile-app)
3. [Admin API (Web Dashboard)](#3-admin-api-web-dashboard)
4. [Quy trình triển khai](#4-quy-trình-triển-khai)
5. [Best Practices](#5-best-practices)

---

## **1. TỔNG QUAN KIẾN TRÚC**

### 🎯 **Vai trò của CoreAPI**

CoreAPI (Laravel 12) đóng vai trò là **BFF (Backend For Frontend)** - Gateway API chính:

- ✅ **REST API** cho Mobile App (Client)
- ✅ **Inertia.js SPA** cho Admin Dashboard
- ✅ **Event Publisher** - Gửi events tới các microservices
- ✅ **Authentication Gateway** - Quản lý user/admin sessions
- ✅ **Master Data** - Lưu trữ users, reports, agencies

### 📊 **Phân biệt 2 tác nhân**

| Tác nhân | Giao diện | Authentication | Chức năng chính |
|----------|-----------|----------------|-----------------|
| **Client** | React Native Mobile App | JWT/Sanctum Token | Tạo phản ánh, xem bản đồ, nhận thông báo, quản lý wallet |
| **Admin** | Inertia.js (Vue/React) | Session-based (Web Guard) | Quản lý reports, users, agencies, dashboard, analytics |

---

## **2. CLIENT API (MOBILE APP)**

### 📱 **Tổng quan**

**Base URL:** `https://api.cityresq360.com/api/v1`  
**Authentication:** Bearer Token (Laravel Sanctum)  
**Content-Type:** `application/json`

### 🔐 **2.1. Authentication Module**

#### **Mục đích:**
Quản lý đăng ký, đăng nhập, profile người dùng.

#### **Endpoints cần implement:**

```yaml
POST   /api/v1/auth/register          # Đăng ký tài khoản
POST   /api/v1/auth/login             # Đăng nhập
POST   /api/v1/auth/logout            # Đăng xuất
POST   /api/v1/auth/refresh           # Refresh token
GET    /api/v1/auth/me                # Lấy thông tin profile
PUT    /api/v1/auth/profile           # Cập nhật profile
POST   /api/v1/auth/change-password   # Đổi mật khẩu
POST   /api/v1/auth/forgot-password   # Quên mật khẩu
POST   /api/v1/auth/reset-password    # Reset mật khẩu
POST   /api/v1/auth/verify-email      # Xác thực email
POST   /api/v1/auth/verify-phone      # Xác thực số điện thoại
POST   /api/v1/auth/update-fcm-token  # Cập nhật FCM push token
```

#### **Chi tiết implementation:**

**File:** `routes/api.php` (cần tạo mới)
```php
<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
            Route::put('profile', [AuthController::class, 'updateProfile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::post('verify-email', [AuthController::class, 'verifyEmail']);
            Route::post('verify-phone', [AuthController::class, 'verifyPhone']);
            Route::post('update-fcm-token', [AuthController::class, 'updateFcmToken']);
        });
    });
});
```

**File:** `app/Http/Controllers/Api/V1/AuthController.php` (cần tạo)
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Models\NguoiDung;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register new user
     * 
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = NguoiDung::create([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'mat_khau' => Hash::make($request->mat_khau),
            'so_dien_thoai' => $request->so_dien_thoai,
            'vai_tro' => 0, // citizen
            'trang_thai' => 1, // active
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login user
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = NguoiDung::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->mat_khau, $user->mat_khau)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng',
            ], 401);
        }

        if ($user->trang_thai === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản đã bị khóa',
            ], 403);
        }

        // Revoke old tokens
        $user->tokens()->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Get authenticated user
     * 
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => auth()->user(),
        ]);
    }

    /**
     * Logout user
     * 
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công',
        ]);
    }

    // TODO: Implement các methods còn lại
    // - updateProfile()
    // - changePassword()
    // - forgotPassword()
    // - resetPassword()
    // - verifyEmail()
    // - verifyPhone()
    // - updateFcmToken()
}
```

**Validation Requests cần tạo:**
```bash
php artisan make:request Api/RegisterRequest
php artisan make:request Api/LoginRequest
php artisan make:request Api/UpdateProfileRequest
php artisan make:request Api/ChangePasswordRequest
```

---

### 📝 **2.2. Reports Module (Phản ánh)**

#### **Mục đích:**
Cho phép người dùng tạo, xem, cập nhật phản ánh về các vấn đề đô thị.

#### **Endpoints cần implement:**

```yaml
GET    /api/v1/reports                # Danh sách phản ánh (filter, pagination)
POST   /api/v1/reports                # Tạo phản ánh mới
GET    /api/v1/reports/{id}           # Chi tiết phản ánh
PUT    /api/v1/reports/{id}           # Cập nhật phản ánh (chỉ author)
DELETE /api/v1/reports/{id}           # Xóa phản ánh (chỉ author)
GET    /api/v1/reports/my             # Phản ánh của tôi
GET    /api/v1/reports/nearby         # Phản ánh gần tôi (location-based)
GET    /api/v1/reports/trending       # Phản ánh phổ biến (nhiều upvote)
POST   /api/v1/reports/{id}/vote      # Vote (upvote/downvote)
POST   /api/v1/reports/{id}/view      # Tăng lượt xem
POST   /api/v1/reports/{id}/rate      # Đánh giá sau khi giải quyết
```

#### **Request Body Example - Create Report:**
```json
{
  "tieu_de": "Đường Nguyễn Huệ bị ngập nặng",
  "mo_ta": "Sau cơn mưa sáng nay, đoạn đường từ ngã tư Lê Lợi đến hết đoạn Nguyễn Huệ bị ngập sâu khoảng 30cm, xe máy không thể qua được",
  "danh_muc": 4,
  "uu_tien": 2,
  "vi_do": 10.8231,
  "kinh_do": 106.6297,
  "dia_chi": "Đường Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP.HCM",
  "la_cong_khai": true,
  "the_tags": ["ngập lụt", "giao thông", "khẩn cấp"],
  "media_ids": [123, 456]
}
```

#### **Response Example:**
```json
{
  "success": true,
  "message": "Tạo phản ánh thành công",
  "data": {
    "id": 12345,
    "nguoi_dung_id": 789,
    "tieu_de": "Đường Nguyễn Huệ bị ngập nặng",
    "mo_ta": "...",
    "danh_muc": 4,
    "trang_thai": 0,
    "uu_tien": 2,
    "vi_do": 10.8231,
    "kinh_do": 106.6297,
    "dia_chi": "...",
    "nhan_ai": "Ngập lụt đô thị",
    "do_tin_cay": 0.92,
    "la_cong_khai": true,
    "luot_ung_ho": 0,
    "luot_khong_ung_ho": 0,
    "luot_xem": 1,
    "the_tags": ["ngập lụt", "giao thông", "khẩn cấp"],
    "media": [
      {
        "id": 123,
        "url": "https://storage.cityresq360.com/images/xxx.jpg",
        "type": "image"
      }
    ],
    "user": {
      "id": 789,
      "ho_ten": "Nguyễn Văn A",
      "anh_dai_dien": "..."
    },
    "created_at": "2025-11-20T10:30:00Z",
    "updated_at": "2025-11-20T10:30:00Z"
  }
}
```

#### **Controller implementation:**

**File:** `app/Http/Controllers/Api/V1/ReportController.php` (cần tạo)
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReportRequest;
use App\Http\Requests\Api\UpdateReportRequest;
use App\Models\PhanAnh;
use App\Events\ReportCreated;
use App\Services\AIClassificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private AIClassificationService $aiService
    ) {}

    /**
     * List reports with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = PhanAnh::with(['user', 'agency', 'media'])
            ->where('la_cong_khai', true);

        // Filters
        if ($request->has('danh_muc')) {
            $query->where('danh_muc', $request->danh_muc);
        }

        if ($request->has('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        if ($request->has('uu_tien')) {
            $query->where('uu_tien', $request->uu_tien);
        }

        // Location-based filter
        if ($request->has('vi_do') && $request->has('kinh_do') && $request->has('radius')) {
            // Use Haversine formula for nearby reports
            $lat = $request->vi_do;
            $lon = $request->kinh_do;
            $radius = $request->radius; // km

            $query->selectRaw("
                *,
                (6371 * acos(cos(radians(?)) * cos(radians(vi_do)) * cos(radians(kinh_do) - radians(?)) + sin(radians(?)) * sin(radians(vi_do)))) AS distance
            ", [$lat, $lon, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reports = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'last_page' => $reports->lastPage(),
            ],
        ]);
    }

    /**
     * Create new report
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        // Create report
        $report = PhanAnh::create([
            'nguoi_dung_id' => auth()->id(),
            'tieu_de' => $request->tieu_de,
            'mo_ta' => $request->mo_ta,
            'danh_muc' => $request->danh_muc,
            'uu_tien' => $request->uu_tien ?? 1,
            'vi_do' => $request->vi_do,
            'kinh_do' => $request->kinh_do,
            'dia_chi' => $request->dia_chi,
            'la_cong_khai' => $request->la_cong_khai ?? true,
            'the_tags' => $request->the_tags,
            'trang_thai' => 0, // pending
        ]);

        // AI Classification
        $classification = $this->aiService->classify($request->tieu_de, $request->mo_ta);
        $report->update([
            'nhan_ai' => $classification['label'],
            'do_tin_cay' => $classification['confidence'],
        ]);

        // Attach media
        if ($request->has('media_ids')) {
            $report->media()->attach($request->media_ids);
        }

        // Dispatch event
        event(new ReportCreated($report));

        // Update user stats
        auth()->user()->increment('tong_so_phan_anh');

        return response()->json([
            'success' => true,
            'message' => 'Tạo phản ánh thành công',
            'data' => $report->load(['user', 'media']),
        ], 201);
    }

    /**
     * Get report detail
     */
    public function show(int $id): JsonResponse
    {
        $report = PhanAnh::with(['user', 'agency', 'media', 'comments.user'])
            ->findOrFail($id);

        // Increment view count
        $report->increment('luot_xem');

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Update report (only author)
     */
    public function update(UpdateReportRequest $request, int $id): JsonResponse
    {
        $report = PhanAnh::findOrFail($id);

        // Check authorization
        if ($report->nguoi_dung_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa phản ánh này',
            ], 403);
        }

        // Only allow update if status is pending
        if ($report->trang_thai !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể chỉnh sửa phản ánh đang chờ xử lý',
            ], 400);
        }

        $report->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phản ánh thành công',
            'data' => $report,
        ]);
    }

    /**
     * Delete report (only author)
     */
    public function destroy(int $id): JsonResponse
    {
        $report = PhanAnh::findOrFail($id);

        if ($report->nguoi_dung_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa phản ánh này',
            ], 403);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa phản ánh thành công',
        ]);
    }

    /**
     * Get my reports
     */
    public function myReports(Request $request): JsonResponse
    {
        $reports = PhanAnh::with(['agency', 'media'])
            ->where('nguoi_dung_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * Vote report (upvote/downvote)
     */
    public function vote(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'loai_binh_chon' => 'required|integer|in:1,-1',
        ]);

        $report = PhanAnh::findOrFail($id);
        $userId = auth()->id();

        // Check if user already voted
        $existingVote = $report->votes()->where('nguoi_dung_id', $userId)->first();

        if ($existingVote) {
            // Remove vote if same type, change vote if different
            if ($existingVote->loai_binh_chon === $request->loai_binh_chon) {
                $existingVote->delete();
                
                if ($request->loai_binh_chon === 1) {
                    $report->decrement('luot_ung_ho');
                } else {
                    $report->decrement('luot_khong_ung_ho');
                }
            } else {
                $existingVote->update(['loai_binh_chon' => $request->loai_binh_chon]);
                
                if ($request->loai_binh_chon === 1) {
                    $report->increment('luot_ung_ho');
                    $report->decrement('luot_khong_ung_ho');
                } else {
                    $report->increment('luot_khong_ung_ho');
                    $report->decrement('luot_ung_ho');
                }
            }
        } else {
            // Create new vote
            $report->votes()->create([
                'nguoi_dung_id' => $userId,
                'loai_binh_chon' => $request->loai_binh_chon,
            ]);

            if ($request->loai_binh_chon === 1) {
                $report->increment('luot_ung_ho');
            } else {
                $report->increment('luot_khong_ung_ho');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật vote thành công',
            'data' => [
                'luot_ung_ho' => $report->luot_ung_ho,
                'luot_khong_ung_ho' => $report->luot_khong_ung_ho,
            ],
        ]);
    }

    // TODO: Implement
    // - nearby() - Lấy phản ánh gần vị trí hiện tại
    // - trending() - Phản ánh phổ biến
    // - rate() - Đánh giá sau khi giải quyết
}
```

---

### 💬 **2.3. Comments Module**

#### **Endpoints:**

```yaml
GET    /api/v1/reports/{id}/comments        # Danh sách bình luận
POST   /api/v1/reports/{id}/comments        # Thêm bình luận
PUT    /api/v1/comments/{id}                # Sửa bình luận
DELETE /api/v1/comments/{id}                # Xóa bình luận
POST   /api/v1/comments/{id}/like           # Like bình luận
```

---

### 📷 **2.4. Media Module**

#### **Endpoints:**

```yaml
POST   /api/v1/media/upload                 # Upload ảnh/video
GET    /api/v1/media/{id}                   # Lấy file
DELETE /api/v1/media/{id}                   # Xóa file
```

#### **Upload Flow:**
1. Client upload file lên CoreAPI
2. CoreAPI forward request đến **Media Service**
3. Media Service upload lên MinIO/S3
4. Trả về URL và metadata

---

### 🗺️ **2.5. Map Module**

#### **Endpoints:**

```yaml
GET    /api/v1/map/reports                  # Lấy tất cả reports cho map
GET    /api/v1/map/heatmap                  # Heatmap data
GET    /api/v1/map/clusters                 # Cluster markers
GET    /api/v1/map/routes                   # GTFS routes
```

---

### 💰 **2.6. Wallet Module**

#### **Endpoints:**

```yaml
GET    /api/v1/wallet                       # Số dư CityPoint
GET    /api/v1/wallet/transactions          # Lịch sử giao dịch
POST   /api/v1/wallet/redeem                # Đổi điểm
GET    /api/v1/wallet/rewards               # Quà thưởng có thể đổi
```

---

### 🔔 **2.7. Notifications Module**

#### **Endpoints:**

```yaml
GET    /api/v1/notifications                # Danh sách thông báo
GET    /api/v1/notifications/unread         # Thông báo chưa đọc
POST   /api/v1/notifications/{id}/read      # Đánh dấu đã đọc
POST   /api/v1/notifications/read-all       # Đánh dấu tất cả đã đọc
DELETE /api/v1/notifications/{id}           # Xóa thông báo
PUT    /api/v1/notifications/settings       # Cài đặt thông báo
```

---

### 📊 **2.8. Dashboard/Stats Module**

#### **Endpoints:**

```yaml
GET    /api/v1/stats/overview               # Thống kê tổng quan
GET    /api/v1/stats/categories             # Thống kê theo danh mục
GET    /api/v1/stats/timeline               # Timeline chart
GET    /api/v1/stats/leaderboard            # Bảng xếp hạng người dùng
```

---

### 🏢 **2.9. Agencies Module**

#### **Endpoints:**

```yaml
GET    /api/v1/agencies                     # Danh sách cơ quan
GET    /api/v1/agencies/{id}                # Chi tiết cơ quan
GET    /api/v1/agencies/{id}/reports        # Phản ánh của cơ quan
GET    /api/v1/agencies/{id}/stats          # Thống kê cơ quan
```

---

## **3. ADMIN API (WEB DASHBOARD)**

### 🖥️ **Tổng quan**

**Base URL:** `https://admin.cityresq360.com/admin`  
**Authentication:** Session-based (Laravel Web Guard)  
**Framework:** Inertia.js + Vue 3 / React

### 🔐 **3.1. Admin Authentication**

**Đã implement trong `routes/admin.php`:**

```php
GET    /admin/login                         # Show login form
POST   /admin/login                         # Login
POST   /admin/logout                        # Logout
GET    /admin/                              # Dashboard
```

**Controller:** `App\Http\Controllers\Admin\AuthController`

---

### 📊 **3.2. Dashboard Module**

#### **Route:** `/admin/dashboard`

**Controller:** `App\Http\Controllers\Admin\DashboardController`

#### **Chức năng:**
- Hiển thị thống kê tổng quan (tổng reports, users, agencies)
- Chart: Reports theo thời gian, danh mục, trạng thái
- Map: Heatmap của reports
- Top users, top agencies
- Realtime updates (WebSocket)

#### **Implementation:**

**File:** `app/Http/Controllers/Admin/DashboardController.php`
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhanAnh;
use App\Models\NguoiDung;
use App\Models\CoQuanXuLy;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Overall stats
        $stats = [
            'total_reports' => PhanAnh::count(),
            'pending_reports' => PhanAnh::where('trang_thai', 0)->count(),
            'resolved_reports' => PhanAnh::where('trang_thai', 3)->count(),
            'total_users' => NguoiDung::count(),
            'total_agencies' => CoQuanXuLy::count(),
            'average_response_time' => PhanAnh::whereNotNull('thoi_gian_phan_hoi_thuc_te')
                ->avg('thoi_gian_phan_hoi_thuc_te'), // minutes
        ];

        // Reports by category
        $reportsByCategory = PhanAnh::select('danh_muc', DB::raw('count(*) as total'))
            ->groupBy('danh_muc')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $this->getCategoryName($item->danh_muc),
                    'total' => $item->total,
                ];
            });

        // Reports by status
        $reportsByStatus = PhanAnh::select('trang_thai', DB::raw('count(*) as total'))
            ->groupBy('trang_thai')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $this->getStatusName($item->trang_thai),
                    'total' => $item->total,
                ];
            });

        // Recent reports
        $recentReports = PhanAnh::with(['user', 'agency'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top users
        $topUsers = NguoiDung::orderBy('diem_uy_tin', 'desc')
            ->limit(10)
            ->get(['id', 'ho_ten', 'anh_dai_dien', 'diem_uy_tin', 'tong_so_phan_anh']);

        // Map data (for heatmap)
        $mapData = PhanAnh::select('vi_do', 'kinh_do', 'danh_muc', 'uu_tien')
            ->where('la_cong_khai', true)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'reportsByCategory' => $reportsByCategory,
            'reportsByStatus' => $reportsByStatus,
            'recentReports' => $recentReports,
            'topUsers' => $topUsers,
            'mapData' => $mapData,
        ]);
    }

    private function getCategoryName(int $category): string
    {
        return match($category) {
            0 => 'Giao thông',
            1 => 'Môi trường',
            2 => 'Cháy nổ',
            3 => 'Rác thải',
            4 => 'Ngập lụt',
            5 => 'Khác',
            default => 'Unknown',
        };
    }

    private function getStatusName(int $status): string
    {
        return match($status) {
            0 => 'Chờ xử lý',
            1 => 'Đã xác nhận',
            2 => 'Đang xử lý',
            3 => 'Đã giải quyết',
            4 => 'Từ chối',
            default => 'Unknown',
        };
    }
}
```

---

### 📝 **3.3. Reports Management**

**Đã có routes trong `routes/admin.php`:**

```php
GET    /admin/reports                       # Danh sách phản ánh
GET    /admin/reports/{id}                  # Chi tiết phản ánh
PATCH  /admin/reports/{id}/status           # Cập nhật trạng thái
PATCH  /admin/reports/{id}/priority         # Cập nhật độ ưu tiên
DELETE /admin/reports/{id}                  # Xóa phản ánh
```

**Controller:** `App\Http\Controllers\Admin\ReportController`

#### **Chức năng cần implement:**
- ✅ Xem danh sách reports với filter (category, status, priority, date range)
- ✅ Search reports (title, description, address)
- ✅ Cập nhật trạng thái (pending → verified → in_progress → resolved/rejected)
- ✅ Gán cơ quan xử lý
- ✅ Cập nhật độ ưu tiên
- ✅ Xem chi tiết + timeline xử lý
- ✅ Export reports (CSV, Excel)
- ✅ Bulk actions (cập nhật nhiều reports cùng lúc)

---

### 👥 **3.4. Users Management**

**Đã có routes:**

```php
GET    /admin/users                         # Danh sách người dùng
GET    /admin/users/{id}                    # Chi tiết user
PATCH  /admin/users/{id}/status             # Khóa/mở khóa user
POST   /admin/users/{id}/verify             # Xác thực công dân (KYC)
POST   /admin/users/{id}/points             # Cộng/trừ CityPoint
DELETE /admin/users/{id}                    # Xóa user
```

**Controller:** `App\Http\Controllers\Admin\UserController`

#### **Chức năng:**
- Quản lý danh sách users (citizen, officer)
- Xem lịch sử phản ánh của user
- Khóa/mở khóa tài khoản
- Xác thực công dân (KYC)
- Thưởng/phạt CityPoint
- Export user list

---

### 🏢 **3.5. Agencies Management**

**Đã có routes (chỉ SuperAdmin & Data Admin):**

```php
GET    /admin/agencies                      # Danh sách cơ quan
GET    /admin/agencies/create               # Form tạo mới
POST   /admin/agencies                      # Tạo cơ quan
GET    /admin/agencies/{id}                 # Chi tiết
GET    /admin/agencies/{id}/edit            # Form sửa
PATCH  /admin/agencies/{id}                 # Cập nhật
DELETE /admin/agencies/{id}                 # Xóa
```

**Controller:** `App\Http\Controllers\Admin\AgencyController`

#### **Chức năng:**
- CRUD agencies
- Xem thống kê hiệu suất (số reports xử lý, thời gian phản hồi trung bình)
- Gán officer vào agency
- Export agency stats

---

### 📊 **3.6. Analytics & Reports (Admin)**

#### **Routes cần thêm:**

```php
GET    /admin/analytics                     # Analytics dashboard
GET    /admin/analytics/performance         # Performance metrics
GET    /admin/analytics/trends              # Trends analysis
GET    /admin/analytics/export              # Export reports
```

#### **Chức năng:**
- Charts: Reports theo thời gian, địa điểm, category
- Heatmap: Khu vực có nhiều reports nhất
- Performance metrics: Response time, resolution time
- Agency performance comparison
- Export analytics data (PDF, Excel)

---

### ⚙️ **3.7. System Settings**

#### **Routes cần thêm:**

```php
GET    /admin/settings                      # System settings
PATCH  /admin/settings                      # Update settings
GET    /admin/logs                          # System logs
GET    /admin/api-versions                  # API versions management
```

#### **Chức năng:**
- Cấu hình hệ thống (thời gian phản hồi, quy tắc thưởng điểm)
- Quản lý API versions
- Xem system logs
- Database backup/restore

---

## **4. QUY TRÌNH TRIỂN KHAI**

### 📅 **Timeline đề xuất**

#### **Week 1: Foundation & Client Auth**
```bash
Day 1-2: Setup cơ bản
  - Tạo routes/api.php
  - Cài đặt Laravel Sanctum
  - Cấu hình CORS

Day 3-4: Client Authentication
  - AuthController (register, login, logout)
  - Validation requests
  - API tests

Day 5-7: Client Reports Module (Phase 1)
  - ReportController (CRUD)
  - Validation
  - Event publishing (ReportCreated)
  - Tests
```

#### **Week 2: Client Reports Advanced & Admin**
```bash
Day 1-3: Client Reports Advanced
  - Vote system
  - Comments
  - Media upload integration
  - Nearby reports
  - Trending reports

Day 4-5: Admin Dashboard
  - DashboardController
  - Stats aggregation
  - Charts data
  - Real-time updates

Day 6-7: Admin Reports Management
  - ReportController (Admin)
  - Status updates
  - Agency assignment
  - Bulk actions
```

#### **Week 3: Advanced Features**
```bash
Day 1-2: Wallet Module (Client)
  - WalletController
  - Transaction history
  - Reward calculation

Day 3-4: Notifications Module (Client)
  - NotificationController
  - FCM integration
  - WebSocket events

Day 5-7: Admin Users & Agencies
  - UserController (Admin)
  - AgencyController (Admin)
  - KYC verification
  - Performance stats
```

---

### 🛠️ **Các bước triển khai cho mỗi module**

#### **Bước 1: Database Migrations**
```bash
php artisan make:migration create_table_name
php artisan migrate
```

#### **Bước 2: Models**
```bash
php artisan make:model ModelName
```

**Định nghĩa relationships trong model:**
```php
// app/Models/PhanAnh.php
public function user() {
    return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
}

public function agency() {
    return $this->belongsTo(CoQuanXuLy::class, 'co_quan_phu_trach_id');
}

public function comments() {
    return $this->hasMany(BinhLuanPhanAnh::class, 'phan_anh_id');
}
```

#### **Bước 3: Controllers**
```bash
php artisan make:controller Api/V1/ResourceController --api
php artisan make:controller Admin/ResourceController
```

#### **Bước 4: Validation Requests**
```bash
php artisan make:request StoreResourceRequest
php artisan make:request UpdateResourceRequest
```

#### **Bước 5: API Resources (Response formatting)**
```bash
php artisan make:resource ResourceResource
php artisan make:resource ResourceCollection
```

#### **Bước 6: Events & Listeners**
```bash
php artisan make:event ResourceCreated
php artisan make:listener PublishResourceCreatedEvent
```

#### **Bước 7: Tests**
```bash
php artisan make:test ResourceTest
php artisan test
```

#### **Bước 8: API Documentation**
- Update OpenAPI/Swagger specs
- Generate Postman collections

---

## **5. BEST PRACTICES**

### ✅ **API Response Format**

**Success Response:**
```json
{
  "success": true,
  "message": "Action completed successfully",
  "data": {...},
  "meta": {
    "page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### ✅ **Authentication Headers**

**Client API:**
```http
Authorization: Bearer {sanctum_token}
Content-Type: application/json
Accept: application/json
X-App-Version: 1.0.0
X-Device-ID: {device_unique_id}
```

**Admin:**
```http
Cookie: laravel_session={session_id}
X-CSRF-TOKEN: {csrf_token}
```

### ✅ **Validation Rules**

**Common rules:**
```php
'email' => 'required|email|unique:nguoi_dungs,email',
'mat_khau' => 'required|min:8|confirmed',
'so_dien_thoai' => 'nullable|regex:/^0[0-9]{9}$/',
'vi_do' => 'required|numeric|between:-90,90',
'kinh_do' => 'required|numeric|between:-180,180',
```

### ✅ **Query Optimization**

```php
// Eager loading
$reports = PhanAnh::with(['user', 'agency', 'media'])->get();

// Select specific columns
$reports = PhanAnh::select(['id', 'tieu_de', 'trang_thai'])->get();

// Indexing
Schema::table('phan_anhs', function (Blueprint $table) {
    $table->index(['trang_thai', 'danh_muc']);
    $table->index('created_at');
});
```

### ✅ **Error Handling**

```php
try {
    // Code
} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    return response()->json([
        'success' => false,
        'message' => 'Resource not found',
    ], 404);
} catch (\Exception $e) {
    \Log::error('Error: ' . $e->getMessage());
    return response()->json([
        'success' => false,
        'message' => 'Internal server error',
    ], 500);
}
```

### ✅ **Event Publishing**

```php
use App\Events\ReportCreated;

// After creating report
event(new ReportCreated($report));
```

**Event class:**
```php
<?php

namespace App\Events;

use App\Models\PhanAnh;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PhanAnh $report) {}
}
```

**Listener (publish to RabbitMQ/Kafka):**
```php
<?php

namespace App\Listeners;

use App\Events\ReportCreated;
use Illuminate\Support\Facades\Queue;

class PublishReportCreatedEvent
{
    public function handle(ReportCreated $event): void
    {
        // Publish to message queue
        Queue::connection('rabbitmq')->push('reports.created', [
            'event_id' => \Str::uuid(),
            'event_type' => 'ReportCreated',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'report_id' => $event->report->id,
                'user_id' => $event->report->nguoi_dung_id,
                'category' => $event->report->danh_muc,
                'location' => [
                    'lat' => $event->report->vi_do,
                    'lon' => $event->report->kinh_do,
                ],
            ],
        ]);
    }
}
```

### ✅ **Testing**

```php
<?php

namespace Tests\Feature\Api;

use App\Models\NguoiDung;
use App\Models\PhanAnh;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_report(): void
    {
        $user = NguoiDung::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/reports', [
                'tieu_de' => 'Test Report',
                'mo_ta' => 'Test description',
                'danh_muc' => 0,
                'vi_do' => 10.8231,
                'kinh_do' => 106.6297,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('phan_anhs', [
            'tieu_de' => 'Test Report',
        ]);
    }
}
```

---

## **📚 RESOURCES**

### **Laravel Documentation**
- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [Sanctum](https://laravel.com/docs/12.x/sanctum)
- [Inertia.js](https://inertiajs.com/)
- [Laravel Events](https://laravel.com/docs/12.x/events)

### **API Standards**
- [REST API Best Practices](https://restfulapi.net/)
- [HTTP Status Codes](https://httpstatuses.com/)
- [OpenAPI Specification](https://swagger.io/specification/)

### **Testing**
- [Laravel Testing](https://laravel.com/docs/12.x/testing)
- [PHPUnit](https://phpunit.de/)

---

## **🎯 CHECKLIST**

### **Client API (Mobile App)**
- [ ] Authentication Module
  - [ ] Register
  - [ ] Login
  - [ ] Logout
  - [ ] Profile management
  - [ ] Password reset
  - [ ] FCM token update
- [ ] Reports Module
  - [ ] List reports
  - [ ] Create report
  - [ ] Update report
  - [ ] Delete report
  - [ ] My reports
  - [ ] Nearby reports
  - [ ] Trending reports
  - [ ] Vote system
  - [ ] Rate report
- [ ] Comments Module
  - [ ] List comments
  - [ ] Add comment
  - [ ] Edit comment
  - [ ] Delete comment
  - [ ] Like comment
- [ ] Media Module
  - [ ] Upload file
  - [ ] Get file
  - [ ] Delete file
- [ ] Map Module
  - [ ] Map markers
  - [ ] Heatmap
  - [ ] Clusters
  - [ ] GTFS routes
- [ ] Wallet Module
  - [ ] Get balance
  - [ ] Transaction history
  - [ ] Redeem points
  - [ ] Rewards catalog
- [ ] Notifications Module
  - [ ] List notifications
  - [ ] Mark as read
  - [ ] Delete notification
  - [ ] Settings
- [ ] Dashboard/Stats
  - [ ] Overview stats
  - [ ] Categories stats
  - [ ] Timeline chart
  - [ ] Leaderboard
- [ ] Agencies
  - [ ] List agencies
  - [ ] Agency detail
  - [ ] Agency stats

### **Admin Web Dashboard**
- [ ] Authentication
  - [ ] Login
  - [ ] Logout
  - [ ] Session management
- [ ] Dashboard
  - [ ] Overview stats
  - [ ] Charts
  - [ ] Recent reports
  - [ ] Top users
  - [ ] Map heatmap
- [ ] Reports Management
  - [ ] List reports
  - [ ] Report detail
  - [ ] Update status
  - [ ] Update priority
  - [ ] Assign agency
  - [ ] Delete report
  - [ ] Bulk actions
  - [ ] Export data
- [ ] Users Management
  - [ ] List users
  - [ ] User detail
  - [ ] Block/unblock user
  - [ ] KYC verification
  - [ ] Add/subtract points
  - [ ] Delete user
  - [ ] Export users
- [ ] Agencies Management
  - [ ] CRUD agencies
  - [ ] Agency stats
  - [ ] Assign officers
  - [ ] Performance metrics
- [ ] Analytics
  - [ ] Performance dashboard
  - [ ] Trends analysis
  - [ ] Export reports
- [ ] System Settings
  - [ ] System config
  - [ ] System logs
  - [ ] API versions
  - [ ] Backup/restore

### **Integration**
- [ ] Event publishing (RabbitMQ/Kafka)
- [ ] AI/ML Service integration
- [ ] Media Service integration
- [ ] Notification Service integration
- [ ] Wallet Service integration
- [ ] Search Service integration

### **Testing**
- [ ] Unit tests
- [ ] Feature tests
- [ ] Integration tests
- [ ] API tests (Postman/Insomnia)

### **Documentation**
- [ ] API documentation (OpenAPI/Swagger)
- [ ] Postman collections
- [ ] README.md
- [ ] Deployment guide

---

**Last Updated:** November 20, 2025  
**Version:** 1.0.0  
**Status:** Ready to implement 🚀
