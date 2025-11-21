<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\QuanTriVien;
use App\Models\NhatKyHeThong;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLogin(): Response
    {
        return Inertia::render('admin/auth/Login', [
            'canResetPassword' => false,
            'status' => session('status'),
        ]);
    }

    /**
     * Handle admin login
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        // Find admin by email or username from DB
        $admin = QuanTriVien::where('email', $request->email)
                            ->orWhere('ten_quan_tri', $request->email)
                            ->first();

        // Check if admin exists
        if (!$admin) {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->onlyInput('email');
        }

        // Check credentials (password might be plain text or already hashed)
        $passwordMatches = Hash::check($request->mat_khau, $admin->mat_khau);

        if (!$passwordMatches) {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->onlyInput('email');
        }

        // Check if admin account is active
        if ($admin->trang_thai === QuanTriVien::TRANG_THAI_LOCKED) {
            return back()->withErrors([
                'email' => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên hệ thống.',
            ])->onlyInput('email');
        }

        // Login with admin guard
        Auth::guard('admin')->login($admin, $request->boolean('remember'));

        // Regenerate session for security
        $request->session()->regenerate();

        // Update last login time - Get fresh instance from DB
        QuanTriVien::where('id', $admin->id)->update([
            'lan_dang_nhap_cuoi' => now(),
        ]);

        // Log admin login activity
        $this->logActivity($admin, 'login', 'Admin đăng nhập vào hệ thống', $request);

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        // Log logout activity
        if ($admin) {
            $this->logActivity($admin, 'logout', 'Admin đăng xuất khỏi hệ thống', $request);
        }

        // Logout from admin guard
        Auth::guard('admin')->logout();

        // Invalidate session and regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Show admin profile
     */
    public function profile(): Response
    {
        $admin = Auth::guard('admin')->user();

        return Inertia::render('admin/profile/Show', [
            'admin' => $admin,
        ]);
    }

    /**
     * Update admin profile
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $authAdmin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'ten_quan_tri' => 'required|string|max:255',
            'email' => 'required|email|unique:quan_tri_viens,email,' . $authAdmin->id,
            'anh_dai_dien' => 'nullable|string|max:255',
        ]);

        // Get admin from DB to update
        $admin = QuanTriVien::findOrFail($authAdmin->id);
        $admin->update($validated);

        // Log profile update
        $this->logActivity($admin, 'update_profile', 'Admin cập nhật thông tin cá nhân', $request);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Change admin password
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $authAdmin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'mat_khau' => 'required|string|min:8|confirmed',
        ]);

        // Get admin from DB
        $admin = QuanTriVien::findOrFail($authAdmin->id);

        // Verify current password
        if (!Hash::check($validated['current_password'], $admin->mat_khau)) {
            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không chính xác.',
            ]);
        }

        // Update password (mutator will auto-hash)
        $admin->update([
            'mat_khau' => $validated['mat_khau'],
        ]);

        // Log password change
        $this->logActivity($admin, 'change_password', 'Admin thay đổi mật khẩu', $request);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    /**
     * Log admin activity to system logs
     *
     * @param QuanTriVien $admin
     * @param string $action
     * @param string $description
     * @param Request $request
     * @return void
     */
    private function logActivity(QuanTriVien $admin, string $action, string $description, Request $request): void
    {
        try {
            NhatKyHeThong::create([
                'nguoi_thuc_hien_id' => $admin->id,
                'loai_nguoi_thuc_hien' => 'admin',
                'hanh_dong' => $action,
                'mo_ta' => $description,
                'dia_chi_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'du_lieu_cu' => null,
                'du_lieu_moi' => null,
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the flow
            Log::error('Failed to log admin activity: ' . $e->getMessage());
        }
    }
}
