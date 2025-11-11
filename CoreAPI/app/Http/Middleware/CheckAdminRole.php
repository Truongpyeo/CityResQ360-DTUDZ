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
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int  ...$roles
     */
    public function handle(Request $request, Closure $next, int ...$roles): Response
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            abort(403, 'Unauthorized');
        }

        // Check if admin has required role
        if (!in_array($admin->vai_tro, $roles)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
