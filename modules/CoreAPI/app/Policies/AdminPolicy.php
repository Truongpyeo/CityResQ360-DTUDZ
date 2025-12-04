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

use App\Models\QuanTriVien;
use Illuminate\Auth\Access\Response;

class AdminPolicy
{
    /**
     * Determine whether the admin can view any admins.
     */
    public function viewAny(QuanTriVien $admin): bool
    {
        // Only SuperAdmin can view admin list
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can view another admin.
     */
    public function view(QuanTriVien $admin, QuanTriVien $targetAdmin): bool
    {
        // SuperAdmin can view all, admins can only view themselves
        return $admin->is_master || $admin->id === $targetAdmin->id;
    }

    /**
     * Determine whether the admin can create admins.
     */
    public function create(QuanTriVien $admin): bool
    {
        // Only SuperAdmin can create new admins
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can update another admin.
     */
    public function update(QuanTriVien $admin, QuanTriVien $targetAdmin): bool
    {
        // SuperAdmin can update all, admins can only update themselves
        if ($admin->is_master) {
            return true;
        }

        return $admin->id === $targetAdmin->id;
    }

    /**
     * Determine whether the admin can delete another admin.
     */
    public function delete(QuanTriVien $admin, QuanTriVien $targetAdmin): bool
    {
        // Cannot delete yourself
        if ($admin->id === $targetAdmin->id) {
            return false;
        }

        // Cannot delete master admin
        if ($targetAdmin->is_master) {
            return false;
        }

        // Only SuperAdmin can delete admins
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can restore another admin.
     */
    public function restore(QuanTriVien $admin, QuanTriVien $targetAdmin): bool
    {
        // Only SuperAdmin can restore
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can permanently delete another admin.
     */
    public function forceDelete(QuanTriVien $admin, QuanTriVien $targetAdmin): bool
    {
        // Only SuperAdmin can force delete (and not master admin)
        return $admin->is_master && !$targetAdmin->is_master;
    }

    /**
     * Determine whether the admin can update another admin's role.
     */
    public function updateRole(QuanTriVien $admin, QuanTriVien $targetAdmin): bool
    {
        // Cannot change own role
        if ($admin->id === $targetAdmin->id) {
            return false;
        }

        // Cannot change master admin role
        if ($targetAdmin->is_master) {
            return false;
        }

        // Only SuperAdmin can change roles
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can update another admin's status.
     */
    public function updateStatus(QuanTriVien $admin, QuanTriVien $targetAdmin): bool
    {
        // Cannot change own status
        if ($admin->id === $targetAdmin->id) {
            return false;
        }

        // Cannot change master admin status
        if ($targetAdmin->is_master) {
            return false;
        }

        // Only SuperAdmin can lock/unlock admins
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can change another admin's password.
     */
    public function changePassword(QuanTriVien $admin, QuanTriVien $targetAdmin): bool
    {
        // SuperAdmin can change all passwords, admins can only change their own
        return $admin->is_master || $admin->id === $targetAdmin->id;
    }
}
