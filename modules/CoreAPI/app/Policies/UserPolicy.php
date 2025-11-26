<?php

namespace App\Policies;

use App\Models\NguoiDung;
use App\Models\QuanTriVien;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the admin can view any users.
     */
    public function viewAny(QuanTriVien $admin): bool
    {
        // All admins can view users
        return true;
    }

    /**
     * Determine whether the admin can view the user.
     */
    public function view(QuanTriVien $admin, NguoiDung $nguoiDung): bool
    {
        // All admins can view user details
        return true;
    }

    /**
     * Determine whether the admin can create users.
     */
    public function create(QuanTriVien $admin): bool
    {
        // Only SuperAdmin can create users manually
        return $admin->is_master || $admin->hasPermission('users.create');
    }

    /**
     * Determine whether the admin can update the user.
     */
    public function update(QuanTriVien $admin, NguoiDung $nguoiDung): bool
    {
        // All admins can update users
        return true;
    }

    /**
     * Determine whether the admin can delete the user.
     */
    public function delete(QuanTriVien $admin, NguoiDung $nguoiDung): bool
    {
        // Only SuperAdmin can delete users
        return $admin->is_master || $admin->hasPermission('users.delete');
    }

    /**
     * Determine whether the admin can restore the user.
     */
    public function restore(QuanTriVien $admin, NguoiDung $nguoiDung): bool
    {
        // Only SuperAdmin can restore
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can permanently delete the user.
     */
    public function forceDelete(QuanTriVien $admin, NguoiDung $nguoiDung): bool
    {
        // Only SuperAdmin can force delete
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can verify users.
     */
    public function verify(QuanTriVien $admin, NguoiDung $nguoiDung): bool
    {
        // All admins can verify users (KYC)
        return true;
    }

    /**
     * Determine whether the admin can update user status.
     */
    public function updateStatus(QuanTriVien $admin, NguoiDung $nguoiDung): bool
    {
        // All admins can lock/unlock users
        return true;
    }

    /**
     * Determine whether the admin can manage user points.
     */
    public function managePoints(QuanTriVien $admin, NguoiDung $nguoiDung): bool
    {
        // All admins can add/subtract points
        return true;
    }
}
