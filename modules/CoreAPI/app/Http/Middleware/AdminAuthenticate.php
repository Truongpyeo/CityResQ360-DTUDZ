<?php

namespace App\Http\Middleware;

use App\Models\NhatKyHeThong;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $mode  Mode: null = require auth, 'guest' = require guest, 'track' = track activity
     */
    public function handle(Request $request, Closure $next, ?string $mode = null): Response
    {
        // Mode: guest (redirect if already authenticated)
        if ($mode === 'guest') {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('admin.dashboard');
            }
            return $next($request);
        }

        // Mode: require authentication (default)
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return redirect()->route('admin.login');
        }

        // Check if admin account is active
        $admin = Auth::guard('admin')->user();
        if ($admin->trang_thai === 0) {
            Auth::guard('admin')->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản đã bị khóa.',
                ], 403);
            }

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Tài khoản đã bị khóa.']);
        }

        // Process request
        $response = $next($request);

        // Track activity if in track mode
        if ($mode === 'track') {
            $this->trackActivity($request);
        }

        return $response;
    }

    /**
     * Track admin activity to system logs
     */
    private function trackActivity(Request $request): void
    {
        try {
            $admin = Auth::guard('admin')->user();
            $method = $request->method();
            $path = $request->path();

            // Skip logging for certain routes (to avoid noise)
            $skipPaths = ['admin', 'admin/', 'admin/dashboard', 'admin/profile'];
            if (in_array($path, $skipPaths) && $method === 'GET') {
                return;
            }

            // Determine action and description
            $action = $this->determineAction($method, $path);
            $description = $this->generateDescription($method, $path);

            NhatKyHeThong::create([
                'nguoi_thuc_hien_id' => $admin->id,
                'loai_nguoi_thuc_hien' => 'admin',
                'hanh_dong' => $action,
                'mo_ta' => $description,
                'dia_chi_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'du_lieu_cu' => null,
                'du_lieu_moi' => $method !== 'GET' ? json_encode($request->except(['_token', '_method', 'mat_khau', 'password'])) : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to track admin activity: ' . $e->getMessage());
        }
    }

    /**
     * Determine action from method and path
     */
    private function determineAction(string $method, string $path): string
    {
        if (str_contains($path, 'reports')) {
            return match($method) {
                'POST' => 'create_report',
                'PUT', 'PATCH' => 'update_report',
                'DELETE' => 'delete_report',
                default => 'view_report',
            };
        }

        if (str_contains($path, 'users')) {
            return match($method) {
                'POST' => 'create_user',
                'PUT', 'PATCH' => 'update_user',
                'DELETE' => 'delete_user',
                default => 'view_user',
            };
        }

        if (str_contains($path, 'agencies')) {
            return match($method) {
                'POST' => 'create_agency',
                'PUT', 'PATCH' => 'update_agency',
                'DELETE' => 'delete_agency',
                default => 'view_agency',
            };
        }

        return 'admin_action';
    }

    /**
     * Generate human-readable description
     */
    private function generateDescription(string $method, string $path): string
    {
        $pathParts = explode('/', $path);
        $resource = $pathParts[1] ?? 'unknown';

        return match($method) {
            'GET' => "Xem {$resource}",
            'POST' => "Tạo mới {$resource}",
            'PUT', 'PATCH' => "Cập nhật {$resource}",
            'DELETE' => "Xóa {$resource}",
            default => "Thao tác trên {$resource}",
        };
    }
}
