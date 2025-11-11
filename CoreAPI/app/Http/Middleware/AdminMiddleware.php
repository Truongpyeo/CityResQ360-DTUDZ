<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated admin
        if (!auth()->guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = auth()->guard('admin')->user();

        // Check if admin is active
        if (!$admin->isActive()) {
            auth()->guard('admin')->logout();
            return redirect()->route('admin.login')
                ->withErrors(['message' => 'Tài khoản của bạn đã bị khóa.']);
        }

        return $next($request);
    }
}
