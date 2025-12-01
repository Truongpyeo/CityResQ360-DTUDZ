<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return Inertia::render('auth/Login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user by email
        $user = NguoiDung::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->mat_khau)) {
            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        // Check if user is banned
        if ($user->trang_thai === NguoiDung::TRANG_THAI_BANNED) {
            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa.',
            ]);
        }

        // Login using web guard
        Auth::guard('web')->login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended('/client');
    }

    /**
     * Show register form
     */
    public function showRegister()
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:nguoi_dungs,email',
            'password' => 'required|min:6|confirmed',
            'so_dien_thoai' => 'required|string|max:20',
        ]);

        $user = NguoiDung::create([
            'ho_ten' => $validated['ho_ten'],
            'email' => $validated['email'],
            'mat_khau' => Hash::make($validated['password']),
            'so_dien_thoai' => $validated['so_dien_thoai'],
            'vai_tro' => NguoiDung::VAI_TRO_CITIZEN,
            'trang_thai' => NguoiDung::TRANG_THAI_ACTIVE,
            'diem_thanh_pho' => 0,
            'xac_thuc_cong_dan' => false,
            'diem_uy_tin' => 50,
            'tong_so_phan_anh' => 0,
            'so_phan_anh_chinh_xac' => 0,
            'ty_le_chinh_xac' => 0.0,
            'cap_huy_hieu' => NguoiDung::HUY_HIEU_BRONZE,
            'tuy_chon_thong_bao' => [
                'email' => true,
                'push' => false,
                'sms' => false,
            ],
        ]);

        Auth::guard('web')->login($user);

        return redirect('/client')->with('success', 'Đăng ký thành công!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
