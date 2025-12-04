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

namespace App\Policies;

use App\Models\CoQuanXuLy;
use App\Models\QuanTriVien;
use Illuminate\Auth\Access\Response;

class AgencyPolicy
{
    /**
     * Determine whether the admin can view any agencies.
     */
    public function viewAny(QuanTriVien $admin): bool
    {
        // All admins can view agencies
        return true;
    }

    /**
     * Determine whether the admin can view the agency.
     */
    public function view(QuanTriVien $admin, CoQuanXuLy $coQuanXuLy): bool
    {
        // All admins can view agency details
        return true;
    }

    /**
     * Determine whether the admin can create agencies.
     */
    public function create(QuanTriVien $admin): bool
    {
        // Only SuperAdmin or Data Admin can create agencies
        return $admin->is_master || $admin->hasPermission('agencies.create');
    }

    /**
     * Determine whether the admin can update the agency.
     */
    public function update(QuanTriVien $admin, CoQuanXuLy $coQuanXuLy): bool
    {
        // Only SuperAdmin or Data Admin can update agencies
        return $admin->is_master || $admin->hasPermission('agencies.update');
    }

    /**
     * Determine whether the admin can delete the agency.
     */
    public function delete(QuanTriVien $admin, CoQuanXuLy $coQuanXuLy): bool
    {
        // Only SuperAdmin can delete agencies
        // Check if agency has reports
        if ($coQuanXuLy->phanAnhs()->count() > 0) {
            return false; // Cannot delete agency with reports
        }

        return $admin->is_master || $admin->hasPermission('agencies.delete');
    }

    /**
     * Determine whether the admin can restore the agency.
     */
    public function restore(QuanTriVien $admin, CoQuanXuLy $coQuanXuLy): bool
    {
        // Only SuperAdmin can restore
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can permanently delete the agency.
     */
    public function forceDelete(QuanTriVien $admin, CoQuanXuLy $coQuanXuLy): bool
    {
        // Only SuperAdmin can force delete
        return $admin->is_master;
    }
}
