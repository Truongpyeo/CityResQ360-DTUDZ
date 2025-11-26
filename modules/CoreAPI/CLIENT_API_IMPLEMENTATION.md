# 🚀 Core API - Client API Implementation Guide

> **Hướng dẫn implement Client API endpoints cho Mobile App**

**Tech Stack:** Laravel 12 + PHP 8.4 + Sanctum  
**Status:** ⏳ In Progress (45%)  
**Priority:** 🔴 CRITICAL - Cần ngay để Mobile App hoạt động

---

## 📋 **MỤC LỤC**

1. [Setup Sanctum](#1-setup-sanctum)
2. [Authentication API](#2-authentication-api)
3. [Reports API](#3-reports-api)
4. [Comments API](#4-comments-api)
5. [Voting API](#5-voting-api)
6. [User Profile API](#6-user-profile-api)
7. [Map API](#7-map-api)
8. [Testing](#8-testing)

---

## **1. SETUP SANCTUM**

### ✅ **Step 1: Cài đặt Sanctum** (DONE)

```bash
php artisan install:api
```

### ✅ **Step 2: Config Sanctum**

**File: `config/sanctum.php`**

```php
<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort()
    ))),

    'guard' => ['web'],

    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
```

### ✅ **Step 3: Migrate Database**

```bash
php artisan migrate
```

Tạo bảng `personal_access_tokens` để lưu API tokens.

---

## **2. AUTHENTICATION API**

### 📝 **Step 1: Tạo Auth Controller**

**File: `app/Http/Controllers/API/AuthController.php`**

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:nguoi_dungs,email',
            'so_dien_thoai' => 'required|string|max:20|unique:nguoi_dungs,so_dien_thoai',
            'mat_khau' => 'required|string|min:8|confirmed',
            'dia_chi' => 'nullable|string|max:500',
            'vi_do' => 'nullable|numeric|between:-90,90',
            'kinh_do' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = NguoiDung::create([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'so_dien_thoai' => $request->so_dien_thoai,
            'mat_khau' => Hash::make($request->mat_khau),
            'dia_chi' => $request->dia_chi,
            'vi_do' => $request->vi_do,
            'kinh_do' => $request->kinh_do,
            'vai_tro' => 0, // 0: citizen
            'trang_thai' => 1, // 1: active
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'ho_ten' => $user->ho_ten,
                    'email' => $user->email,
                    'so_dien_thoai' => $user->so_dien_thoai,
                    'anh_dai_dien' => $user->anh_dai_dien,
                    'vai_tro' => $user->vai_tro,
                ],
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Đăng nhập
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'mat_khau' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = NguoiDung::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->mat_khau, $user->mat_khau)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        if ($user->trang_thai !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản đã bị khóa'
            ], 403);
        }

        // Revoke old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'ho_ten' => $user->ho_ten,
                    'email' => $user->email,
                    'so_dien_thoai' => $user->so_dien_thoai,
                    'anh_dai_dien' => $user->anh_dai_dien,
                    'vai_tro' => $user->vai_tro,
                    'diem_thanh_tich' => $user->diem_thanh_tich,
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token đã được làm mới',
            'data' => [
                'token' => $token
            ]
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'ho_ten' => $user->ho_ten,
                'email' => $user->email,
                'so_dien_thoai' => $user->so_dien_thoai,
                'anh_dai_dien' => $user->anh_dai_dien,
                'vai_tro' => $user->vai_tro,
                'dia_chi' => $user->dia_chi,
                'vi_do' => $user->vi_do,
                'kinh_do' => $user->kinh_do,
                'diem_thanh_tich' => $user->diem_thanh_tich,
                'ngay_tao' => $user->ngay_tao,
            ]
        ]);
    }
}
```

---

## **3. REPORTS API**

### 📝 **Step 1: Tạo Report Controller**

**File: `app/Http/Controllers/API/ReportController.php`**

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PhanAnh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * Danh sách phản ánh
     */
    public function index(Request $request)
    {
        $query = PhanAnh::query()
            ->with(['nguoiDung:id,ho_ten,anh_dai_dien', 'coQuanXuLy:id,ten_co_quan'])
            ->orderBy('ngay_tao', 'desc');

        // Filter by status
        if ($request->has('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Filter by category
        if ($request->has('danh_muc')) {
            $query->where('danh_muc', $request->danh_muc);
        }

        // Filter by priority
        if ($request->has('muc_do_uu_tien')) {
            $query->where('muc_do_uu_tien', $request->muc_do_uu_tien);
        }

        // Near me (radius in km)
        if ($request->has('vi_do') && $request->has('kinh_do')) {
            $lat = $request->vi_do;
            $lng = $request->kinh_do;
            $radius = $request->ban_kinh ?? 5; // default 5km

            $query->whereRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(vi_do)) * cos(radians(kinh_do) - radians(?)) + sin(radians(?)) * sin(radians(vi_do)))) <= ?
            ", [$lat, $lng, $lat, $radius]);
        }

        $reports = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ]
        ]);
    }

    /**
     * Tạo phản ánh mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'required|string',
            'danh_muc' => 'required|integer|between:0,11',
            'vi_do' => 'required|numeric|between:-90,90',
            'kinh_do' => 'required|numeric|between:-180,180',
            'dia_chi_chi_tiet' => 'required|string|max:500',
            'anh_minhs' => 'nullable|array',
            'anh_minhs.*' => 'string', // Media IDs from MediaService
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Mock AI classification (TEMPORARY - sẽ gọi AI Service sau)
        $aiClassification = $this->mockAIClassification($request->mo_ta);

        $report = PhanAnh::create([
            'nguoi_dung_id' => $request->user()->id,
            'tieu_de' => $request->tieu_de,
            'mo_ta' => $request->mo_ta,
            'danh_muc' => $request->danh_muc,
            'vi_do' => $request->vi_do,
            'kinh_do' => $request->kinh_do,
            'dia_chi_chi_tiet' => $request->dia_chi_chi_tiet,
            'anh_minhs' => $request->anh_minhs ? json_encode($request->anh_minhs) : null,
            'trang_thai' => 0, // 0: pending
            'muc_do_uu_tien' => $this->calculatePriority($request->danh_muc),
            'nhan_ai' => $aiClassification['nhan'],
            'do_tin_cay' => $aiClassification['do_tin_cay'],
        ]);

        // TODO: Publish event to RabbitMQ/Kafka
        // Event::dispatch(new ReportCreated($report));

        return response()->json([
            'success' => true,
            'message' => 'Tạo phản ánh thành công',
            'data' => $report->load(['nguoiDung:id,ho_ten,anh_dai_dien'])
        ], 201);
    }

    /**
     * Chi tiết phản ánh
     */
    public function show($id)
    {
        $report = PhanAnh::with([
            'nguoiDung:id,ho_ten,anh_dai_dien,diem_thanh_tich',
            'coQuanXuLy:id,ten_co_quan,dia_chi',
            'binhLuans' => function ($query) {
                $query->with('nguoiDung:id,ho_ten,anh_dai_dien')
                      ->orderBy('ngay_tao', 'desc')
                      ->limit(5);
            }
        ])->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phản ánh'
            ], 404);
        }

        // Increment view count
        $report->increment('luot_xem');

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Cập nhật phản ánh (chỉ người tạo)
     */
    public function update(Request $request, $id)
    {
        $report = PhanAnh::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phản ánh'
            ], 404);
        }

        // Check permission
        if ($report->nguoi_dung_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền sửa phản ánh này'
            ], 403);
        }

        // Only allow update if status is pending
        if ($report->trang_thai !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể sửa phản ánh đang chờ xử lý'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'tieu_de' => 'sometimes|required|string|max:255',
            'mo_ta' => 'sometimes|required|string',
            'danh_muc' => 'sometimes|required|integer|between:0,11',
            'anh_minhs' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $report->update($request->only(['tieu_de', 'mo_ta', 'danh_muc']));

        if ($request->has('anh_minhs')) {
            $report->anh_minhs = json_encode($request->anh_minhs);
            $report->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phản ánh thành công',
            'data' => $report
        ]);
    }

    /**
     * Xóa phản ánh (soft delete)
     */
    public function destroy(Request $request, $id)
    {
        $report = PhanAnh::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phản ánh'
            ], 404);
        }

        // Check permission
        if ($report->nguoi_dung_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa phản ánh này'
            ], 403);
        }

        // Only allow delete if status is pending
        if ($report->trang_thai !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa phản ánh đang chờ xử lý'
            ], 400);
        }

        $report->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Xóa phản ánh thành công'
        ]);
    }

    /**
     * Phản ánh của tôi
     */
    public function myReports(Request $request)
    {
        $reports = PhanAnh::where('nguoi_dung_id', $request->user()->id)
            ->with('coQuanXuLy:id,ten_co_quan')
            ->orderBy('ngay_tao', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'total' => $reports->total(),
            ]
        ]);
    }

    /**
     * Mock AI classification (TEMPORARY)
     */
    private function mockAIClassification($moTa)
    {
        // Random classification cho demo
        $categories = [
            'hạ tầng', 'môi trường', 'giao thông', 
            'an ninh', 'y tế', 'giáo dục'
        ];

        return [
            'nhan' => $categories[array_rand($categories)],
            'do_tin_cay' => rand(70, 95) / 100, // 0.70 - 0.95
        ];
    }

    /**
     * Calculate priority based on category
     */
    private function calculatePriority($category)
    {
        $highPriority = [3, 4, 9]; // An ninh, Y tế, Thiên tai
        $mediumPriority = [0, 1, 2, 5, 6]; // Hạ tầng, Môi trường, Giao thông, PCCC, Điện nước
        
        if (in_array($category, $highPriority)) {
            return 2; // High
        } elseif (in_array($category, $mediumPriority)) {
            return 1; // Medium
        }
        
        return 0; // Low
    }
}
```

---

## **4. COMMENTS API**

**File: `app/Http/Controllers/API/CommentController.php`**

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BinhLuanPhanAnh;
use App\Models\PhanAnh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Danh sách bình luận của 1 phản ánh
     */
    public function index(Request $request, $reportId)
    {
        $report = PhanAnh::find($reportId);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phản ánh'
            ], 404);
        }

        $comments = BinhLuanPhanAnh::where('phan_anh_id', $reportId)
            ->with('nguoiDung:id,ho_ten,anh_dai_dien')
            ->orderBy('ngay_tao', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $comments->items(),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'total' => $comments->total(),
            ]
        ]);
    }

    /**
     * Thêm bình luận
     */
    public function store(Request $request, $reportId)
    {
        $report = PhanAnh::find($reportId);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phản ánh'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'noi_dung' => 'required|string|max:1000',
            'anh_minhs' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $comment = BinhLuanPhanAnh::create([
            'phan_anh_id' => $reportId,
            'nguoi_dung_id' => $request->user()->id,
            'noi_dung' => $request->noi_dung,
            'anh_minhs' => $request->anh_minhs ? json_encode($request->anh_minhs) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm bình luận thành công',
            'data' => $comment->load('nguoiDung:id,ho_ten,anh_dai_dien')
        ], 201);
    }

    /**
     * Xóa bình luận
     */
    public function destroy(Request $request, $reportId, $commentId)
    {
        $comment = BinhLuanPhanAnh::where('phan_anh_id', $reportId)
            ->where('id', $commentId)
            ->first();

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bình luận'
            ], 404);
        }

        // Check permission
        if ($comment->nguoi_dung_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa bình luận này'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa bình luận thành công'
        ]);
    }
}
```

---

## **5. VOTING API**

**File: `app/Http/Controllers/API/VoteController.php`**

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BinhChonPhanAnh;
use App\Models\PhanAnh;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    /**
     * Vote phản ánh
     */
    public function vote(Request $request, $reportId)
    {
        $report = PhanAnh::find($reportId);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phản ánh'
            ], 404);
        }

        $userId = $request->user()->id;

        // Check if already voted
        $existingVote = BinhChonPhanAnh::where('phan_anh_id', $reportId)
            ->where('nguoi_dung_id', $userId)
            ->first();

        if ($existingVote) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã vote phản ánh này rồi'
            ], 400);
        }

        BinhChonPhanAnh::create([
            'phan_anh_id' => $reportId,
            'nguoi_dung_id' => $userId,
        ]);

        // Increment vote count
        $report->increment('so_luot_binh_chon');

        return response()->json([
            'success' => true,
            'message' => 'Vote thành công',
            'data' => [
                'so_luot_binh_chon' => $report->so_luot_binh_chon
            ]
        ]);
    }

    /**
     * Unvote phản ánh
     */
    public function unvote(Request $request, $reportId)
    {
        $vote = BinhChonPhanAnh::where('phan_anh_id', $reportId)
            ->where('nguoi_dung_id', $request->user()->id)
            ->first();

        if (!$vote) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa vote phản ánh này'
            ], 400);
        }

        $vote->delete();

        // Decrement vote count
        $report = PhanAnh::find($reportId);
        $report->decrement('so_luot_binh_chon');

        return response()->json([
            'success' => true,
            'message' => 'Bỏ vote thành công',
            'data' => [
                'so_luot_binh_chon' => $report->so_luot_binh_chon
            ]
        ]);
    }
}
```

---

## **6. USER PROFILE API**

**File: `app/Http/Controllers/API/UserController.php`**

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Cập nhật profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ho_ten' => 'sometimes|required|string|max:255',
            'so_dien_thoai' => 'sometimes|required|string|max:20|unique:nguoi_dungs,so_dien_thoai,' . $request->user()->id,
            'dia_chi' => 'nullable|string|max:500',
            'anh_dai_dien' => 'nullable|string', // Media ID
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update($request->only(['ho_ten', 'so_dien_thoai', 'dia_chi', 'anh_dai_dien']));

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công',
            'data' => $user
        ]);
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mat_khau_cu' => 'required|string',
            'mat_khau_moi' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->mat_khau_cu, $user->mat_khau)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu cũ không đúng'
            ], 400);
        }

        $user->mat_khau = Hash::make($request->mat_khau_moi);
        $user->save();

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại'
        ]);
    }
}
```

---

## **7. MAP API**

**File: `app/Http/Controllers/API/MapController.php`**

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PhanAnh;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * Get reports near location
     */
    public function nearby(Request $request)
    {
        $lat = $request->input('vi_do');
        $lng = $request->input('kinh_do');
        $radius = $request->input('ban_kinh', 5); // default 5km

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu tọa độ vị trí'
            ], 400);
        }

        $reports = PhanAnh::selectRaw("
            id, tieu_de, danh_muc, vi_do, kinh_do, dia_chi_chi_tiet, 
            trang_thai, muc_do_uu_tien, ngay_tao,
            (6371 * acos(cos(radians(?)) * cos(radians(vi_do)) * cos(radians(kinh_do) - radians(?)) + sin(radians(?)) * sin(radians(vi_do)))) AS khoang_cach
        ", [$lat, $lng, $lat])
            ->having('khoang_cach', '<=', $radius)
            ->orderBy('khoang_cach', 'asc')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    /**
     * Get heatmap data
     */
    public function heatmap(Request $request)
    {
        $reports = PhanAnh::select('vi_do', 'kinh_do', 'muc_do_uu_tien')
            ->where('trang_thai', '!=', 3) // exclude resolved
            ->get()
            ->map(function ($report) {
                return [
                    'lat' => $report->vi_do,
                    'lng' => $report->kinh_do,
                    'weight' => $report->muc_do_uu_tien + 1, // 1-3
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }
}
```

---

## **8. ROUTES**

**File: `routes/api.php`**

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\VoteController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MapController;

Route::prefix('v1')->group(function () {
    
    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Map public data
    Route::get('/map/nearby', [MapController::class, 'nearby']);
    Route::get('/map/heatmap', [MapController::class, 'heatmap']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        
        // Reports
        Route::get('/reports', [ReportController::class, 'index']);
        Route::post('/reports', [ReportController::class, 'store']);
        Route::get('/reports/my', [ReportController::class, 'myReports']);
        Route::get('/reports/{id}', [ReportController::class, 'show']);
        Route::put('/reports/{id}', [ReportController::class, 'update']);
        Route::delete('/reports/{id}', [ReportController::class, 'destroy']);
        
        // Comments
        Route::get('/reports/{reportId}/comments', [CommentController::class, 'index']);
        Route::post('/reports/{reportId}/comments', [CommentController::class, 'store']);
        Route::delete('/reports/{reportId}/comments/{commentId}', [CommentController::class, 'destroy']);
        
        // Voting
        Route::post('/reports/{reportId}/vote', [VoteController::class, 'vote']);
        Route::delete('/reports/{reportId}/vote', [VoteController::class, 'unvote']);
        
        // User Profile
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::post('/user/change-password', [UserController::class, 'changePassword']);
    });
});
```

---

## **9. TESTING**

### 🧪 **Test với cURL**

```bash
# 1. Register
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "ho_ten": "Nguyễn Văn A",
    "email": "nguyenvana@example.com",
    "so_dien_thoai": "0901234567",
    "mat_khau": "password123",
    "mat_khau_confirmation": "password123"
  }'

# 2. Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "nguyenvana@example.com",
    "mat_khau": "password123"
  }'

# 3. Create Report
curl -X POST http://localhost:8000/api/v1/reports \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tieu_de": "Ổ gà trên đường Lê Lợi",
    "mo_ta": "Nhiều ổ gà lớn gây nguy hiểm cho người tham gia giao thông",
    "danh_muc": 2,
    "vi_do": 10.7769,
    "kinh_do": 106.7009,
    "dia_chi_chi_tiet": "123 Lê Lợi, Quận 1, TP.HCM"
  }'

# 4. Get Reports
curl http://localhost:8000/api/v1/reports \
  -H "Authorization: Bearer YOUR_TOKEN"

# 5. Vote Report
curl -X POST http://localhost:8000/api/v1/reports/1/vote \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## **10. NEXT STEPS**

### ✅ **Hoàn thành:**
- [x] Setup Sanctum
- [x] Auth API (Register, Login, Logout)
- [x] Reports API (CRUD)
- [x] Comments API
- [x] Voting API
- [x] User Profile API
- [x] Map API

### ⏳ **Đang làm:**
- [ ] Tích hợp Media Service
- [ ] Event publishing (RabbitMQ/Kafka)
- [ ] Notification Service integration

### 📋 **Cần làm tiếp:**
- [ ] API Resources (transform response)
- [ ] Form Requests (validation classes)
- [ ] Unit tests
- [ ] API documentation (Swagger/OpenAPI)
- [ ] Rate limiting
- [ ] API versioning

---

**Last Updated:** November 22, 2025  
**Status:** ⏳ 70% Complete  
**Priority:** 🔴 CRITICAL
