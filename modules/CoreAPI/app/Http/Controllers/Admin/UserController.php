<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Http\Requests\Admin\AddUserPointsRequest;
use App\Models\NguoiDung;
use App\Models\NhatKyHeThong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display list of users
     */
    public function index(Request $request)
    {
        $query = NguoiDung::query();

        // Filter by status
        if ($request->has('trang_thai') && $request->trang_thai !== '') {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Filter by verified status
        if ($request->has('xac_thuc_cong_dan') && $request->xac_thuc_cong_dan !== '') {
            $query->where('xac_thuc_cong_dan', $request->xac_thuc_cong_dan === '1');
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(20)->through(function ($user) {
            return [
                'id' => $user->id,
                'ho_ten' => $user->ho_ten,
                'email' => $user->email,
                'so_dien_thoai' => $user->so_dien_thoai,
                'vai_tro' => $user->vai_tro,
                'vai_tro_text' => $user->vai_tro === NguoiDung::VAI_TRO_CITIZEN ? 'Công dân' : 'Cán bộ',
                'trang_thai' => $user->trang_thai,
                'trang_thai_text' => $user->trang_thai === NguoiDung::TRANG_THAI_ACTIVE ? 'Hoạt động' : 'Đã khóa',
                'diem_thanh_pho' => $user->diem_thanh_pho,
                'xac_thuc_cong_dan' => $user->xac_thuc_cong_dan,
                'diem_uy_tin' => $user->diem_uy_tin,
                'tong_so_phan_anh' => $user->tong_so_phan_anh,
                'ty_le_chinh_xac' => $user->ty_le_chinh_xac,
                'cap_huy_hieu' => $this->getBadgeName($user->cap_huy_hieu),
                'created_at' => $user->created_at->format('d/m/Y H:i'),
            ];
        });

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => $request->only(['trang_thai', 'xac_thuc_cong_dan', 'search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display user details
     */
    public function show($id)
    {
        $user = NguoiDung::with(['phanAnhs', 'binhLuans', 'binhChons'])->findOrFail($id);

        return Inertia::render('admin/users/Show', [
            'user' => [
                'id' => $user->id,
                'ho_ten' => $user->ho_ten,
                'email' => $user->email,
                'so_dien_thoai' => $user->so_dien_thoai,
                'vai_tro' => $user->vai_tro,
                'anh_dai_dien' => $user->anh_dai_dien,
                'trang_thai' => $user->trang_thai,
                'diem_thanh_pho' => $user->diem_thanh_pho,
                'xac_thuc_cong_dan' => $user->xac_thuc_cong_dan,
                'diem_uy_tin' => $user->diem_uy_tin,
                'tong_so_phan_anh' => $user->tong_so_phan_anh,
                'so_phan_anh_chinh_xac' => $user->so_phan_anh_chinh_xac,
                'ty_le_chinh_xac' => $user->ty_le_chinh_xac,
                'cap_huy_hieu' => $user->cap_huy_hieu,
                'push_token' => $user->push_token,
                'tuy_chon_thong_bao' => $user->tuy_chon_thong_bao,
                'created_at' => $user->created_at->format('d/m/Y H:i'),
                'updated_at' => $user->updated_at->format('d/m/Y H:i'),
                'phan_anhs' => $user->phanAnhs->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'tieu_de' => $report->tieu_de,
                        'trang_thai' => $report->getStatusName(),
                        'created_at' => $report->created_at->format('d/m/Y'),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Update user information
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = NguoiDung::findOrFail($id);
        $user->update($request->validated());

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_UPDATE,
            NhatKyHeThong::LOAI_NGUOI_DUNG,
            $user->id,
            ['action' => 'update_info', 'data' => $request->validated()]
        );

        return redirect()->back()->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Update user status
     */
    public function updateStatus(UpdateUserStatusRequest $request, $id)
    {
        $user = NguoiDung::findOrFail($id);
        $user->update(['trang_thai' => $request->trang_thai]);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_UPDATE,
            NhatKyHeThong::LOAI_NGUOI_DUNG,
            $user->id,
            ['action' => 'update_status', 'trang_thai' => $request->trang_thai]
        );

        $message = $request->trang_thai === NguoiDung::TRANG_THAI_ACTIVE
            ? 'Kích hoạt tài khoản thành công!'
            : 'Khóa tài khoản thành công!';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Verify user
     */
    public function verify($id)
    {
        $user = NguoiDung::findOrFail($id);
        $user->update(['xac_thuc_cong_dan' => true]);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_UPDATE,
            NhatKyHeThong::LOAI_NGUOI_DUNG,
            $user->id,
            ['action' => 'verify_citizen']
        );

        return redirect()->back()->with('success', 'Xác thực công dân thành công!');
    }

    /**
     * Add city points to user
     */
    public function addPoints(AddUserPointsRequest $request, $id)
    {
        $user = NguoiDung::findOrFail($id);
        $user->addCityPoints($request->diem);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_UPDATE,
            NhatKyHeThong::LOAI_NGUOI_DUNG,
            $user->id,
            [
                'action' => 'add_points',
                'diem' => $request->diem,
                'ly_do' => $request->ly_do,
            ]
        );

        return redirect()->back()->with('success', "Thêm {$request->diem} điểm thành công!");
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = NguoiDung::findOrFail($id);

        // Authorization check
        if (Gate::forUser(auth()->guard('admin')->user())->denies('delete', $user)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa người dùng này!');
        }

        // Log before delete
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_DELETE,
            NhatKyHeThong::LOAI_NGUOI_DUNG,
            $user->id,
            ['ho_ten' => $user->ho_ten, 'email' => $user->email]
        );

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Xóa người dùng thành công!');
    }

    /**
     * Get badge name from badge level
     */
    private function getBadgeName($badge): string
    {
        return match($badge) {
            NguoiDung::HUY_HIEU_BRONZE => 'bronze',
            NguoiDung::HUY_HIEU_SILVER => 'silver',
            NguoiDung::HUY_HIEU_GOLD => 'gold',
            NguoiDung::HUY_HIEU_PLATINUM => 'platinum',
            default => 'bronze',
        };
    }

    /**
     * Export users to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'vai_tro',
            'trang_thai',
            'xac_thuc_danh_tinh',
            'search'
        ]);

        $filename = 'nguoi-dung-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\UsersExport($filters),
            $filename
        );
    }
}
