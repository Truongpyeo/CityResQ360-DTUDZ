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

namespace Database\Seeders;

use App\Models\ChiTietPhanQuyen;
use App\Models\ChucNang;
use App\Models\VaiTro;
use Illuminate\Database\Seeder;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting RBAC setup...');

        // 1. Create Roles
        $this->createRoles();

        // 2. Create Permissions (Functions)
        $permissions = $this->createPermissions();

        // 3. Assign Permissions to Roles
        $this->assignPermissions($permissions);

        $this->command->info('✅ RBAC setup completed successfully!');
    }

    /**
     * Create roles
     */
    private function createRoles()
    {
        $this->command->info('📋 Creating roles...');

        $roles = [
            [
                'ten_vai_tro' => 'Super Admin',
                'slug' => VaiTro::SLUG_SUPER_ADMIN,
                'mo_ta' => 'Quản trị viên cấp cao nhất - Toàn quyền hệ thống (is_master = 1)',
                'trang_thai' => VaiTro::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_vai_tro' => 'Quản trị dữ liệu',
                'slug' => VaiTro::SLUG_DATA_ADMIN,
                'mo_ta' => 'Quản lý dữ liệu master: cơ quan, danh mục, cấu hình hệ thống',
                'trang_thai' => VaiTro::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_vai_tro' => 'Quản trị cơ quan',
                'slug' => VaiTro::SLUG_AGENCY_ADMIN,
                'mo_ta' => 'Quản lý phản ánh và người dùng thuộc cơ quan',
                'trang_thai' => VaiTro::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_vai_tro' => 'Điều hành viên',
                'slug' => VaiTro::SLUG_MODERATOR,
                'mo_ta' => 'Kiểm duyệt nội dung, xử lý phản ánh cơ bản',
                'trang_thai' => VaiTro::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_vai_tro' => 'Người xem',
                'slug' => VaiTro::SLUG_VIEWER,
                'mo_ta' => 'Chỉ xem thông tin, không có quyền chỉnh sửa',
                'trang_thai' => VaiTro::TRANG_THAI_ACTIVE,
            ],
        ];

        foreach ($roles as $role) {
            $vaiTro = VaiTro::firstOrCreate(
                ['slug' => $role['slug']],
                $role
            );
            if ($vaiTro->wasRecentlyCreated) {
                $this->command->info("  ✓ {$role['ten_vai_tro']} (created)");
            } else {
                $this->command->info("  ✓ {$role['ten_vai_tro']} (already exists)");
            }
        }
    }

    /**
     * Create permissions (functions)
     */
    private function createPermissions(): array
    {
        $this->command->info('🔑 Creating permissions...');

        $permissions = [
            // Dashboard
            [
                'ten_chuc_nang' => 'Xem Dashboard',
                'route_name' => 'admin.dashboard',
                'nhom_chuc_nang' => ChucNang::NHOM_DASHBOARD,
                'mo_ta' => 'Xem trang chủ quản trị',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 1,
            ],

            // Reports Management
            [
                'ten_chuc_nang' => 'Xem danh sách phản ánh',
                'route_name' => 'admin.reports.index',
                'nhom_chuc_nang' => ChucNang::NHOM_REPORTS,
                'mo_ta' => 'Xem danh sách tất cả phản ánh',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 2,
            ],
            [
                'ten_chuc_nang' => 'Xem chi tiết phản ánh',
                'route_name' => 'admin.reports.show',
                'nhom_chuc_nang' => ChucNang::NHOM_REPORTS,
                'mo_ta' => 'Xem chi tiết từng phản ánh',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 3,
            ],
            [
                'ten_chuc_nang' => 'Cập nhật trạng thái phản ánh',
                'route_name' => 'admin.reports.update-status',
                'nhom_chuc_nang' => ChucNang::NHOM_REPORTS,
                'mo_ta' => 'Thay đổi trạng thái xử lý phản ánh',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 4,
            ],
            [
                'ten_chuc_nang' => 'Cập nhật độ ưu tiên',
                'route_name' => 'admin.reports.update-priority',
                'nhom_chuc_nang' => ChucNang::NHOM_REPORTS,
                'mo_ta' => 'Thay đổi độ ưu tiên phản ánh',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 5,
            ],
            [
                'ten_chuc_nang' => 'Xóa phản ánh',
                'route_name' => 'admin.reports.destroy',
                'nhom_chuc_nang' => ChucNang::NHOM_REPORTS,
                'mo_ta' => 'Xóa phản ánh khỏi hệ thống',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 6,
            ],

            // Users Management
            [
                'ten_chuc_nang' => 'Xem danh sách người dùng',
                'route_name' => 'admin.users.index',
                'nhom_chuc_nang' => ChucNang::NHOM_USERS,
                'mo_ta' => 'Xem danh sách tất cả người dùng',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 7,
            ],
            [
                'ten_chuc_nang' => 'Xem chi tiết người dùng',
                'route_name' => 'admin.users.show',
                'nhom_chuc_nang' => ChucNang::NHOM_USERS,
                'mo_ta' => 'Xem thông tin chi tiết người dùng',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 8,
            ],
            [
                'ten_chuc_nang' => 'Khóa/Mở khóa người dùng',
                'route_name' => 'admin.users.update-status',
                'nhom_chuc_nang' => ChucNang::NHOM_USERS,
                'mo_ta' => 'Khóa hoặc mở khóa tài khoản người dùng',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 9,
            ],
            [
                'ten_chuc_nang' => 'Xác thực công dân (KYC)',
                'route_name' => 'admin.users.verify',
                'nhom_chuc_nang' => ChucNang::NHOM_USERS,
                'mo_ta' => 'Xác thực danh tính công dân',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 10,
            ],
            [
                'ten_chuc_nang' => 'Quản lý điểm thưởng',
                'route_name' => 'admin.users.add-points',
                'nhom_chuc_nang' => ChucNang::NHOM_USERS,
                'mo_ta' => 'Cộng/trừ CityPoint cho người dùng',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 11,
            ],
            [
                'ten_chuc_nang' => 'Xóa người dùng',
                'route_name' => 'admin.users.destroy',
                'nhom_chuc_nang' => ChucNang::NHOM_USERS,
                'mo_ta' => 'Xóa tài khoản người dùng',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 12,
            ],

            // Agencies Management
            [
                'ten_chuc_nang' => 'Xem danh sách cơ quan',
                'route_name' => 'admin.agencies.index',
                'nhom_chuc_nang' => ChucNang::NHOM_AGENCIES,
                'mo_ta' => 'Xem danh sách các cơ quan xử lý',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 13,
            ],
            [
                'ten_chuc_nang' => 'Tạo cơ quan mới',
                'route_name' => 'admin.agencies.store',
                'nhom_chuc_nang' => ChucNang::NHOM_AGENCIES,
                'mo_ta' => 'Thêm cơ quan xử lý mới',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 14,
            ],
            [
                'ten_chuc_nang' => 'Cập nhật cơ quan',
                'route_name' => 'admin.agencies.update',
                'nhom_chuc_nang' => ChucNang::NHOM_AGENCIES,
                'mo_ta' => 'Chỉnh sửa thông tin cơ quan',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 15,
            ],
            [
                'ten_chuc_nang' => 'Xóa cơ quan',
                'route_name' => 'admin.agencies.destroy',
                'nhom_chuc_nang' => ChucNang::NHOM_AGENCIES,
                'mo_ta' => 'Xóa cơ quan khỏi hệ thống',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 16,
            ],

            // Analytics
            [
                'ten_chuc_nang' => 'Xem báo cáo thống kê',
                'route_name' => 'admin.analytics',
                'nhom_chuc_nang' => ChucNang::NHOM_ANALYTICS,
                'mo_ta' => 'Xem các báo cáo và biểu đồ thống kê',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 17,
            ],

            // Settings
            [
                'ten_chuc_nang' => 'Quản lý cấu hình hệ thống',
                'route_name' => 'admin.settings',
                'nhom_chuc_nang' => ChucNang::NHOM_SETTINGS,
                'mo_ta' => 'Cấu hình các thông số hệ thống',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 18,
            ],
            [
                'ten_chuc_nang' => 'Xem nhật ký hệ thống',
                'route_name' => 'admin.logs',
                'nhom_chuc_nang' => ChucNang::NHOM_SYSTEM,
                'mo_ta' => 'Xem log hoạt động hệ thống',
                'trang_thai' => ChucNang::TRANG_THAI_ACTIVE,
                'thu_tu' => 19,
            ],
        ];

        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $chucNang = ChucNang::firstOrCreate(
                ['route_name' => $permission['route_name']],
                $permission
            );
            $createdPermissions[] = $chucNang;
            if ($chucNang->wasRecentlyCreated) {
                $this->command->info("  ✓ {$permission['ten_chuc_nang']} (created)");
            } else {
                $this->command->info("  ✓ {$permission['ten_chuc_nang']} (already exists)");
            }
        }

        return $createdPermissions;
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissions(array $permissions)
    {
        $this->command->info('🔗 Assigning permissions to roles...');

        $roles = VaiTro::all()->keyBy('slug');

        // Super Admin - No permissions needed (is_master = 1 grants all access)
        $this->command->info('  ✓ Super Admin: Toàn quyền (is_master)');

        // Data Admin - Agencies + Settings
        $dataAdminPermissions = collect($permissions)->filter(function ($perm) {
            return in_array($perm->nhom_chuc_nang, [
                ChucNang::NHOM_DASHBOARD,
                ChucNang::NHOM_AGENCIES,
                ChucNang::NHOM_SETTINGS,
                ChucNang::NHOM_ANALYTICS,
            ]);
        });
        $this->assignToRole($roles[VaiTro::SLUG_DATA_ADMIN], $dataAdminPermissions);

        // Agency Admin - Reports + Users (read/update)
        $agencyAdminPermissions = collect($permissions)->filter(function ($perm) {
            return in_array($perm->nhom_chuc_nang, [
                ChucNang::NHOM_DASHBOARD,
                ChucNang::NHOM_REPORTS,
                ChucNang::NHOM_USERS,
            ]) && ! str_contains($perm->route_name, 'destroy');
        });
        $this->assignToRole($roles[VaiTro::SLUG_AGENCY_ADMIN], $agencyAdminPermissions);

        // Moderator - Reports (read/update status only)
        $moderatorPermissions = collect($permissions)->filter(function ($perm) {
            return $perm->nhom_chuc_nang === ChucNang::NHOM_DASHBOARD ||
                   ($perm->nhom_chuc_nang === ChucNang::NHOM_REPORTS &&
                    in_array($perm->route_name, ['admin.reports.index', 'admin.reports.show', 'admin.reports.update-status']));
        });
        $this->assignToRole($roles[VaiTro::SLUG_MODERATOR], $moderatorPermissions);

        // Viewer - Read only
        $viewerPermissions = collect($permissions)->filter(function ($perm) {
            return str_contains($perm->route_name, 'index') ||
                   str_contains($perm->route_name, 'show') ||
                   $perm->route_name === 'admin.dashboard';
        });
        $this->assignToRole($roles[VaiTro::SLUG_VIEWER], $viewerPermissions);
    }

    /**
     * Assign permissions to role
     */
    private function assignToRole($role, $permissions)
    {
        $assignedCount = 0;
        foreach ($permissions as $permission) {
            $existing = ChiTietPhanQuyen::where('id_vai_tro', $role->id)
                ->where('id_chuc_nang', $permission->id)
                ->first();

            if (! $existing) {
                ChiTietPhanQuyen::create([
                    'id_vai_tro' => $role->id,
                    'id_chuc_nang' => $permission->id,
                ]);
                $assignedCount++;
            }
        }
        $this->command->info("  ✓ {$role->ten_vai_tro}: {$assignedCount} quyền mới / {$permissions->count()} tổng");
    }
}
