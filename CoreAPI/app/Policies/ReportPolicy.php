<?php

namespace App\Policies;

use App\Models\PhanAnh;
use App\Models\QuanTriVien;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    /**
     * Determine whether the admin can view any reports.
     */
    public function viewAny(QuanTriVien $admin): bool
    {
        // All admins can view reports
        return true;
    }

    /**
     * Determine whether the admin can view the report.
     */
    public function view(QuanTriVien $admin, PhanAnh $phanAnh): bool
    {
        // All admins can view individual reports
        return true;
    }

    /**
     * Determine whether the admin can create reports.
     */
    public function create(QuanTriVien $admin): bool
    {
        // Admins cannot create reports (users do)
        return false;
    }

    /**
     * Determine whether the admin can update the report.
     */
    public function update(QuanTriVien $admin, PhanAnh $phanAnh): bool
    {
        // All admins can update reports (status, priority, etc.)
        return true;
    }

    /**
     * Determine whether the admin can delete the report.
     */
    public function delete(QuanTriVien $admin, PhanAnh $phanAnh): bool
    {
        // Only SuperAdmin or master admin can delete reports
        return $admin->is_master || $admin->hasPermission('reports.delete');
    }

    /**
     * Determine whether the admin can restore the report.
     */
    public function restore(QuanTriVien $admin, PhanAnh $phanAnh): bool
    {
        // Only SuperAdmin can restore
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can permanently delete the report.
     */
    public function forceDelete(QuanTriVien $admin, PhanAnh $phanAnh): bool
    {
        // Only SuperAdmin can force delete
        return $admin->is_master;
    }

    /**
     * Determine whether the admin can update report status.
     */
    public function updateStatus(QuanTriVien $admin, PhanAnh $phanAnh): bool
    {
        // All admins can update status
        return true;
    }

    /**
     * Determine whether the admin can assign agency.
     */
    public function assignAgency(QuanTriVien $admin, PhanAnh $phanAnh): bool
    {
        // All admins can assign agency
        return true;
    }
}
