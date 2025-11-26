<?php

namespace App\Http\Middleware;

use App\Models\QuanTriVien;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     * Check if admin has permission to access specific functionality
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $permission  Optional specific permission to check (route name)
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $authAdmin = auth()->guard('admin')->user();

        if (!$authAdmin) {
            abort(403, 'Unauthorized');
        }

        // If no specific permission required, allow access
        if (!$permission) {
            return $next($request);
        }

        // Get admin from DB with relationships
        $admin = QuanTriVien::with('vaiTro')->find($authAdmin->id);

        // Master admin has all permissions
        if ($admin->is_master) {
            return $next($request);
        }

        // Check permission
        if (!$admin->hasPermission($permission)) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}
