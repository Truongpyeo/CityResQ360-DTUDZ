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
