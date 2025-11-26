<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Requests\Admin\UpdateAdminStatusRequest;
use App\Http\Requests\Admin\UpdateAdminRoleRequest;
use App\Http\Requests\Admin\ChangeAdminPasswordRequest;
use App\Models\QuanTriVien;
use App\Models\VaiTro;
use App\Models\NhatKyHeThong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Display list of admins
     */
    public function index(Request $request)
    {
        $query = QuanTriVien::with('vaiTro');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ten_dang_nhap', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('id_vai_tro')) {
            $query->where('id_vai_tro', $request->id_vai_tro);
        }

        // Filter by status
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $admins = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($admin) {
                return [
                    'id' => $admin->id,
                    'ho_ten' => $admin->ho_ten,
                    'email' => $admin->email,
                    'ten_dang_nhap' => $admin->ten_dang_nhap,
                    'so_dien_thoai' => $admin->so_dien_thoai,
                    'trang_thai' => $admin->trang_thai,
                    'is_master' => $admin->is_master,
                    'vai_tro' => $admin->vaiTro ? [
                        'id' => $admin->vaiTro->id,
                        'ten_vai_tro' => $admin->vaiTro->ten_vai_tro,
                        'slug' => $admin->vaiTro->slug,
                    ] : null,
                    'lan_dang_nhap_cuoi' => $admin->lan_dang_nhap_cuoi
                        ? $admin->lan_dang_nhap_cuoi->format('d/m/Y H:i')
                        : null,
                    'created_at' => $admin->created_at->format('d/m/Y H:i'),
                ];
            });

        // Get all roles for filter
        $roles = VaiTro::where('trang_thai', 1)
            ->orderBy('ten_vai_tro')
            ->get(['id', 'ten_vai_tro']);

        // Stats
        $stats = [
            'total' => QuanTriVien::count(),
            'active' => QuanTriVien::where('trang_thai', 1)->count(),
            'locked' => QuanTriVien::where('trang_thai', 0)->count(),
            'master' => QuanTriVien::where('is_master', true)->count(),
        ];

        return Inertia::render('admin/admins/Index', [
            'admins' => $admins,
            'roles' => $roles,
            'stats' => $stats,
            'filters' => $request->only(['search', 'id_vai_tro', 'trang_thai']),
        ]);
    }

    /**
     * Show create admin form
     */
    public function create()
    {
        $roles = VaiTro::where('trang_thai', 1)
            ->orderBy('ten_vai_tro')
            ->get(['id', 'ten_vai_tro', 'slug']);

        return Inertia::render('admin/admins/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store new admin
     */
    public function store(StoreAdminRequest $request)
    {
        $validated = $request->validated();

        $validated['mat_khau'] = Hash::make($validated['mat_khau']);
        $validated['is_master'] = false; // Only manually set via database

        $admin = QuanTriVien::create($validated);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'create',
            'admin',
            $admin->id,
            ['ho_ten' => $admin->ho_ten, 'email' => $admin->email]
        );

        return redirect()->route('admin.admins.index')
            ->with('success', 'Tạo quản trị viên thành công!');
    }

    /**
     * Show admin detail
     */
    public function show($id)
    {
        $admin = QuanTriVien::with('vaiTro')->findOrFail($id);

        // Get recent activity logs
        $recentLogs = NhatKyHeThong::where('nguoi_dung_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'hanh_dong' => $log->hanh_dong,
                    'doi_tuong' => $log->loai_doi_tuong,
                    'noi_dung' => $log->du_lieu_meta,
                    'dia_chi_ip' => $log->dia_chi_ip,
                    'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                ];
            });

        return Inertia::render('admin/admins/Show', [
            'admin' => [
                'id' => $admin->id,
                'ho_ten' => $admin->ho_ten,
                'email' => $admin->email,
                'ten_dang_nhap' => $admin->ten_dang_nhap,
                'so_dien_thoai' => $admin->so_dien_thoai,
                'trang_thai' => $admin->trang_thai,
                'is_master' => $admin->is_master,
                'vai_tro' => $admin->vaiTro ? [
                    'id' => $admin->vaiTro->id,
                    'ten_vai_tro' => $admin->vaiTro->ten_vai_tro,
                    'slug' => $admin->vaiTro->slug,
                ] : null,
                'lan_dang_nhap_cuoi' => $admin->lan_dang_nhap_cuoi
                    ? $admin->lan_dang_nhap_cuoi->format('d/m/Y H:i')
                    : null,
                'created_at' => $admin->created_at->format('d/m/Y H:i'),
                'updated_at' => $admin->updated_at->format('d/m/Y H:i'),
            ],
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * Show edit admin form
     */
    public function edit($id)
    {
        $admin = QuanTriVien::with('vaiTro')->findOrFail($id);

        $roles = VaiTro::where('trang_thai', 1)
            ->orderBy('ten_vai_tro')
            ->get(['id', 'ten_vai_tro', 'slug']);

        return Inertia::render('admin/admins/Edit', [
            'admin' => [
                'id' => $admin->id,
                'ho_ten' => $admin->ho_ten,
                'email' => $admin->email,
                'ten_dang_nhap' => $admin->ten_dang_nhap,
                'so_dien_thoai' => $admin->so_dien_thoai,
                'id_vai_tro' => $admin->id_vai_tro,
                'trang_thai' => $admin->trang_thai,
                'is_master' => $admin->is_master,
            ],
            'roles' => $roles,
        ]);
    }

    /**
     * Update admin
     */
    public function update(UpdateAdminRequest $request, $id)
    {
        $admin = QuanTriVien::findOrFail($id);

        $validated = $request->validated();

        $admin->update($validated);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'update',
            'admin',
            $admin->id,
            ['ho_ten' => $admin->ho_ten]
        );

        return redirect()->route('admin.admins.index')
            ->with('success', 'Cập nhật quản trị viên thành công!');
    }

    /**
     * Update admin status (lock/unlock)
     */
    public function updateStatus(UpdateAdminStatusRequest $request, $id)
    {
        $admin = QuanTriVien::findOrFail($id);

        // Prevent locking self
        if ($admin->id === auth()->guard('admin')->id()) {
            return back()->with('error', 'Không thể khóa tài khoản của chính bạn!');
        }

        // Prevent locking master admin
        if ($admin->is_master) {
            return back()->with('error', 'Không thể khóa tài khoản Master Admin!');
        }

        $validated = $request->validated();

        $admin->update($validated);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'update_status',
            'admin',
            $admin->id,
            [
                'ho_ten' => $admin->ho_ten,
                'trang_thai' => $validated['trang_thai'] == 1 ? 'active' : 'locked',
            ]
        );

        $message = $validated['trang_thai'] == 1
            ? 'Mở khóa quản trị viên thành công!'
            : 'Khóa quản trị viên thành công!';

        return back()->with('success', $message);
    }

    /**
     * Update admin role
     */
    public function updateRole(UpdateAdminRoleRequest $request, $id)
    {
        $admin = QuanTriVien::findOrFail($id);

        // Prevent changing role of master admin
        if ($admin->is_master) {
            return back()->with('error', 'Không thể thay đổi vai trò của Master Admin!');
        }

        $validated = $request->validated();

        $oldRole = $admin->vaiTro->ten_vai_tro ?? 'N/A';
        $admin->update($validated);
        $newRole = $admin->fresh()->vaiTro->ten_vai_tro ?? 'N/A';

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'update_role',
            'admin',
            $admin->id,
            [
                'ho_ten' => $admin->ho_ten,
                'old_role' => $oldRole,
                'new_role' => $newRole,
            ]
        );

        return back()->with('success', 'Cập nhật vai trò thành công!');
    }

    /**
     * Change admin password
     */
    public function changePassword(ChangeAdminPasswordRequest $request, $id)
    {
        $admin = QuanTriVien::findOrFail($id);

        $validated = $request->validated();

        $admin->update([
            'mat_khau' => Hash::make($validated['mat_khau_moi']),
        ]);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'change_password',
            'admin',
            $admin->id,
            ['ho_ten' => $admin->ho_ten]
        );

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    /**
     * Delete admin
     */
    public function destroy($id)
    {
        $admin = QuanTriVien::findOrFail($id);

        // Prevent deleting self
        if ($admin->id === auth()->guard('admin')->id()) {
            return back()->with('error', 'Không thể xóa tài khoản của chính bạn!');
        }

        // Prevent deleting master admin
        if ($admin->is_master) {
            return back()->with('error', 'Không thể xóa tài khoản Master Admin!');
        }

        // Log before delete
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            'delete',
            'admin',
            $admin->id,
            ['ho_ten' => $admin->ho_ten, 'email' => $admin->email]
        );

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Xóa quản trị viên thành công!');
    }
}
