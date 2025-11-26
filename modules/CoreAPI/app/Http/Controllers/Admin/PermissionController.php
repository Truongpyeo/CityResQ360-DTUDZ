<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Requests\Admin\StoreFunctionRequest;
use App\Http\Requests\Admin\UpdateFunctionRequest;
use App\Http\Requests\Admin\UpdatePermissionsRequest;
use App\Models\VaiTro;
use App\Models\ChucNang;
use App\Models\ChiTietPhanQuyen;
use App\Models\NhatKyHeThong;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display list of roles
     */
    public function roles(Request $request)
    {
        $query = VaiTro::withCount(['admins', 'chucNangs']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ten_vai_tro', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $roles = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($role) {
                return [
                    'id' => $role->id,
                    'ten_vai_tro' => $role->ten_vai_tro,
                    'slug' => $role->slug,
                    'mo_ta' => $role->mo_ta,
                    'trang_thai' => $role->trang_thai,
                    'admins_count' => $role->admins_count,
                    'permissions_count' => $role->chuc_nangs_count,
                    'created_at' => $role->created_at->format('d/m/Y H:i'),
                ];
            });

        return Inertia::render('admin/permissions/Roles', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'trang_thai']),
        ]);
    }

    /**
     * Show create role form
     */
    public function createRole()
    {
        return Inertia::render('admin/permissions/CreateRole');
    }

    /**
     * Store new role
     */
    public function storeRole(StoreRoleRequest $request)
    {
        $role = VaiTro::create($request->validated());

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'create',
            'vai_tro',
            $role->id,
            ['ten_vai_tro' => $role->ten_vai_tro]
        );

        return redirect()->route('admin.permissions.roles')
            ->with('success', 'Tạo vai trò thành công!');
    }

    /**
     * Show edit role form
     */
    public function editRole($id)
    {
        $role = VaiTro::findOrFail($id);

        return Inertia::render('admin/permissions/EditRole', [
            'role' => [
                'id' => $role->id,
                'ten_vai_tro' => $role->ten_vai_tro,
                'slug' => $role->slug,
                'mo_ta' => $role->mo_ta,
                'trang_thai' => $role->trang_thai,
            ],
        ]);
    }

    /**
     * Update role
     */
    public function updateRole(UpdateRoleRequest $request, $id)
    {
        $role = VaiTro::findOrFail($id);

        $role->update($request->validated());

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'update',
            'vai_tro',
            $role->id,
            ['ten_vai_tro' => $role->ten_vai_tro]
        );

        return redirect()->route('admin.permissions.roles')
            ->with('success', 'Cập nhật vai trò thành công!');
    }

    /**
     * Delete role
     */
    public function destroyRole($id)
    {
        $role = VaiTro::findOrFail($id);

        // Check if role has admins
        if ($role->admins()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Không thể xóa vai trò đang có quản trị viên!');
        }

        // Log before delete
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'delete',
            'vai_tro',
            $role->id,
            ['ten_vai_tro' => $role->ten_vai_tro]
        );

        $role->delete();

        return redirect()->route('admin.permissions.roles')
            ->with('success', 'Xóa vai trò thành công!');
    }

    /**
     * Display list of functions/permissions
     */
    public function functions(Request $request)
    {
        $query = ChucNang::withCount('vaiTros');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ten_chuc_nang', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            });
        }

        // Filter by group
        if ($request->filled('nhom_chuc_nang')) {
            $query->where('nhom_chuc_nang', $request->nhom_chuc_nang);
        }

        // Filter by status
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $functions = $query->ordered()
            ->paginate(20)
            ->through(function ($func) {
                return [
                    'id' => $func->id,
                    'ten_chuc_nang' => $func->ten_chuc_nang,
                    'route_name' => $func->route_name,
                    'nhom_chuc_nang' => $func->nhom_chuc_nang,
                    'mo_ta' => $func->mo_ta,
                    'trang_thai' => $func->trang_thai,
                    'thu_tu' => $func->thu_tu,
                    'roles_count' => $func->vai_tros_count,
                    'created_at' => $func->created_at->format('d/m/Y H:i'),
                ];
            });

        return Inertia::render('admin/permissions/Functions', [
            'functions' => $functions,
            'filters' => $request->only(['search', 'nhom_chuc_nang', 'trang_thai']),
        ]);
    }

    /**
     * Show create function form
     */
    public function createFunction()
    {
        return Inertia::render('admin/permissions/CreateFunction');
    }

    /**
     * Store new function
     */
    public function storeFunction(StoreFunctionRequest $request)
    {
        $function = ChucNang::create($request->validated());

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'create',
            'chuc_nang',
            $function->id,
            ['ten_chuc_nang' => $function->ten_chuc_nang]
        );

        return redirect()->route('admin.permissions.functions')
            ->with('success', 'Tạo chức năng thành công!');
    }

    /**
     * Show edit function form
     */
    public function editFunction($id)
    {
        $function = ChucNang::findOrFail($id);

        return Inertia::render('admin/permissions/EditFunction', [
            'function' => [
                'id' => $function->id,
                'ten_chuc_nang' => $function->ten_chuc_nang,
                'route_name' => $function->route_name,
                'nhom_chuc_nang' => $function->nhom_chuc_nang,
                'mo_ta' => $function->mo_ta,
                'trang_thai' => $function->trang_thai,
                'thu_tu' => $function->thu_tu,
            ],
        ]);
    }

    /**
     * Update function
     */
    public function updateFunction(UpdateFunctionRequest $request, $id)
    {
        $function = ChucNang::findOrFail($id);

        $function->update($request->validated());

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'update',
            'chuc_nang',
            $function->id,
            ['ten_chuc_nang' => $function->ten_chuc_nang]
        );

        return redirect()->route('admin.permissions.functions')
            ->with('success', 'Cập nhật chức năng thành công!');
    }

    /**
     * Delete function
     */
    public function destroyFunction($id)
    {
        $function = ChucNang::findOrFail($id);

        // Log before delete
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'delete',
            'chuc_nang',
            $function->id,
            ['ten_chuc_nang' => $function->ten_chuc_nang]
        );

        // Delete associated permissions
        ChiTietPhanQuyen::where('id_chuc_nang', $id)->delete();

        $function->delete();

        return redirect()->route('admin.permissions.functions')
            ->with('success', 'Xóa chức năng thành công!');
    }

    /**
     * Show role permissions assignment page
     */
    public function assignPermissions($roleId)
    {
        $role = VaiTro::with('chucNangs')->findOrFail($roleId);

        // Get all functions grouped by nhom_chuc_nang
        $functions = ChucNang::active()
            ->ordered()
            ->get()
            ->groupBy('nhom_chuc_nang')
            ->map(function ($group) use ($role) {
                return $group->map(function ($func) use ($role) {
                    return [
                        'id' => $func->id,
                        'ten_chuc_nang' => $func->ten_chuc_nang,
                        'route_name' => $func->route_name,
                        'mo_ta' => $func->mo_ta,
                        'has_permission' => $role->chucNangs->contains('id', $func->id),
                    ];
                });
            });

        return Inertia::render('admin/permissions/AssignPermissions', [
            'role' => [
                'id' => $role->id,
                'ten_vai_tro' => $role->ten_vai_tro,
                'slug' => $role->slug,
            ],
            'functions' => $functions,
        ]);
    }

    /**
     * Update role permissions
     */
    public function updatePermissions(UpdatePermissionsRequest $request, $roleId)
    {
        $role = VaiTro::findOrFail($roleId);

        $validated = $request->validated();

        // Delete old permissions
        ChiTietPhanQuyen::where('id_vai_tro', $roleId)->delete();

        // Create new permissions
        foreach ($validated['permissions'] as $functionId) {
            ChiTietPhanQuyen::create([
                'id_vai_tro' => $roleId,
                'id_chuc_nang' => $functionId,
            ]);
        }

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'update',
            'phan_quyen',
            $role->id,
            [
                'vai_tro' => $role->ten_vai_tro,
                'so_quyen' => count($validated['permissions'])
            ]
        );

        return redirect()->route('admin.permissions.roles')
            ->with('success', 'Cập nhật phân quyền thành công!');
    }
}
