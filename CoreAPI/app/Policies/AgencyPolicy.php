<?php

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
