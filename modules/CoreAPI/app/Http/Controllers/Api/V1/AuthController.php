<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */



namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\UpdateFcmTokenRequest;
use App\Http\Requests\Api\Auth\UpdateProfileRequest;
use App\Http\Requests\Api\Auth\VerifyCodeRequest;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    /**
     * Register new user
     * POST /api/v1/auth/register
     */
    public function register(RegisterRequest $request)
    {
        $user = NguoiDung::create([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'mat_khau' => Hash::make($request->mat_khau),
            'so_dien_thoai' => $request->so_dien_thoai,
            'vai_tro' => 0, // Citizen
            'trang_thai' => 1, // Active
            'diem_thanh_pho' => 0,
            'diem_uy_tin' => 50, // Default reputation
            'cap_huy_hieu' => 0, // Bronze
            'xac_thuc_danh_tinh' => false,
        ]);

        // Create Sanctum token
        $token = $user->createToken('mobile_app')->plainTextToken;

        return $this->created([
            'user' => [
                'id' => $user->id,
                'ho_ten' => $user->ho_ten,
                'email' => $user->email,
                'so_dien_thoai' => $user->so_dien_thoai,
                'anh_dai_dien' => $user->anh_dai_dien,
                'vai_tro' => $user->vai_tro,
                'diem_thanh_pho' => $user->diem_thanh_pho,
                'diem_uy_tin' => $user->diem_uy_tin,
                'cap_huy_hieu' => $user->cap_huy_hieu,
                'xac_thuc_danh_tinh' => $user->xac_thuc_danh_tinh,
            ],
            'token' => $token,
        ], 'Đăng ký thành công');
    }

    /**
     * Login user
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request)
    {
        $user = NguoiDung::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->mat_khau, $user->mat_khau)) {
            return $this->error('Email hoặc mật khẩu không đúng', null, 401);
        }

        if ($user->trang_thai !== 1) {
            return $this->error('Tài khoản đã bị khóa', null, 403);
        }

        // Revoke old tokens if not "remember me"
        if (! $request->remember) {
            $user->tokens()->delete();
        }

        // Create new token
        $token = $user->createToken('mobile_app')->plainTextToken;

        return $this->success([
            'user' => [
                'id' => $user->id,
                'ho_ten' => $user->ho_ten,
                'email' => $user->email,
                'so_dien_thoai' => $user->so_dien_thoai,
                'anh_dai_dien' => $user->anh_dai_dien,
                'vai_tro' => $user->vai_tro,
                'diem_thanh_pho' => $user->diem_thanh_pho,
                'diem_uy_tin' => $user->diem_uy_tin,
                'cap_huy_hieu' => $user->cap_huy_hieu,
                'xac_thuc_danh_tinh' => $user->xac_thuc_danh_tinh,
            ],
            'token' => $token,
        ], 'Đăng nhập thành công');
    }

    /**
     * Get current authenticated user
     * GET /api/v1/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // Get statistics
        $totalReports = $user->phanAnhs()->count();
        $resolvedReports = $user->phanAnhs()->where('trang_thai', 3)->count();
        $accuracyRate = $totalReports > 0 ? ($resolvedReports / $totalReports) * 100 : 0;

        return $this->success([
            'id' => $user->id,
            'ho_ten' => $user->ho_ten,
            'email' => $user->email,
            'so_dien_thoai' => $user->so_dien_thoai,
            'anh_dai_dien' => $user->anh_dai_dien,
            'vai_tro' => $user->vai_tro,
            'diem_thanh_pho' => $user->diem_thanh_pho,
            'diem_uy_tin' => $user->diem_uy_tin,
            'cap_huy_hieu' => $user->cap_huy_hieu,
            'xac_thuc_danh_tinh' => $user->xac_thuc_danh_tinh,
            'tong_so_phan_anh' => $totalReports,
            'ty_le_chinh_xac' => round($accuracyRate, 2),
            'ngay_tham_gia' => $user->created_at,
        ]);
    }

    /**
     * Check if user is logged in (verify token and model type)
     * GET /api/v1/auth/check-login
     * 
     * Verifies:
     * - Token is valid (Sanctum guard)
     * - Authenticated user is instance of NguoiDung model
     * - Account is active
     */
    public function checkLogin(Request $request)
    {
        // Get authenticated user via Sanctum guard
        $user = auth('sanctum')->user();

        // Check if user exists and is authenticated
        if (!$user) {
            return $this->error('Chưa đăng nhập', [
                'authenticated' => false,
                'reason' => 'invalid_token'
            ], 401);
        }

        // Verify user is instance of NguoiDung model
        if (!($user instanceof NguoiDung)) {
            return $this->error('Token không hợp lệ cho người dùng', [
                'authenticated' => false,
                'reason' => 'invalid_model',
                'model_type' => get_class($user)
            ], 403);
        }

        // Check if account is active
        if ($user->trang_thai !== 1) {
            return $this->error('Tài khoản đã bị khóa', [
                'authenticated' => false,
                'reason' => 'account_locked',
                'status' => $user->trang_thai
            ], 403);
        }

        // Return success with basic user info
        return $this->success([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'ho_ten' => $user->ho_ten,
                'email' => $user->email,
                'vai_tro' => $user->vai_tro,
                'xac_thuc_danh_tinh' => $user->xac_thuc_danh_tinh,
            ],
            'token_info' => [
                'token_name' => $user->currentAccessToken()?->name,
                'abilities' => $user->currentAccessToken()?->abilities ?? ['*'],
                'last_used_at' => $user->currentAccessToken()?->last_used_at,
            ]
        ], 'Đã đăng nhập');
    }

    /**
     * Logout user
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Đăng xuất thành công');
    }

    /**
     * Refresh token
     * POST /api/v1/auth/refresh
     */
    public function refresh(Request $request)
    {
        $user = $request->user();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('mobile_app')->plainTextToken;

        return $this->success([
            'token' => $token,
        ], 'Token đã được làm mới');
    }

    /**
     * Update profile
     * PUT /api/v1/auth/profile
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $user->update($request->only(['ho_ten', 'so_dien_thoai', 'anh_dai_dien']));

        return $this->success([
            'id' => $user->id,
            'ho_ten' => $user->ho_ten,
            'email' => $user->email,
            'so_dien_thoai' => $user->so_dien_thoai,
            'anh_dai_dien' => $user->anh_dai_dien,
        ], 'Cập nhật thông tin thành công');
    }

    /**
     * Change password
     * POST /api/v1/auth/change-password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->mat_khau_cu, $user->mat_khau)) {
            return $this->error('Mật khẩu cũ không đúng', null, 400);
        }

        $user->update([
            'mat_khau' => Hash::make($request->mat_khau_moi),
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('mobile_app')->plainTextToken;

        return $this->success([
            'token' => $token,
        ], 'Đổi mật khẩu thành công');
    }

    /**
     * Forgot password (send reset link)
     * POST /api/v1/auth/forgot-password
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // TODO: Send password reset email
        // For now, just return success

        return $this->success(null, 'Link đặt lại mật khẩu đã được gửi đến email của bạn');
    }

    /**
     * Reset password
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        // TODO: Verify reset token
        // For now, just update password

        $user = NguoiDung::where('email', $request->email)->first();
        $user->update([
            'mat_khau' => Hash::make($request->mat_khau),
        ]);

        return $this->success(null, 'Đặt lại mật khẩu thành công');
    }

    /**
     * Verify email (with verification code)
     * POST /api/v1/auth/verify-email
     */
    public function verifyEmail(VerifyCodeRequest $request)
    {
        // TODO: Verify email code
        // For now, just mark as verified

        $user = $request->user();
        $user->update([
            'xac_thuc_email' => true,
        ]);

        return $this->success([
            'xac_thuc_email' => true,
        ], 'Xác thực email thành công');
    }

    /**
     * Verify phone (with verification code)
     * POST /api/v1/auth/verify-phone
     */
    public function verifyPhone(VerifyCodeRequest $request)
    {
        // TODO: Verify phone code
        // For now, just mark as verified

        $user = $request->user();
        $user->update([
            'xac_thuc_danh_tinh' => true,
        ]);

        return $this->success([
            'xac_thuc_danh_tinh' => true,
        ], 'Xác thực số điện thoại thành công');
    }

    /**
     * Update FCM token for push notifications
     * POST /api/v1/auth/update-fcm-token
     */
    public function updateFcmToken(UpdateFcmTokenRequest $request)
    {
        $user = $request->user();
        $user->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return $this->success([
            'fcm_token' => $user->fcm_token,
        ], 'Cập nhật FCM token thành công');
    }
}
