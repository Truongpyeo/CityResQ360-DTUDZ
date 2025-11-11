<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoQuanXuLy;
use App\Models\NhatKyHeThong;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgencyController extends Controller
{
    /**
     * Display list of agencies
     */
    public function index(Request $request)
    {
        $query = CoQuanXuLy::query();

        // Filter by status
        if ($request->has('trang_thai') && $request->trang_thai !== '') {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Filter by level
        if ($request->has('cap_do') && $request->cap_do !== '') {
            $query->where('cap_do', $request->cap_do);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ten_co_quan', 'like', "%{$search}%")
                    ->orWhere('email_lien_he', 'like', "%{$search}%")
                    ->orWhere('dia_chi', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $agencies = $query->paginate(20)->through(function ($agency) {
            return [
                'id' => $agency->id,
                'ten_co_quan' => $agency->ten_co_quan,
                'email_lien_he' => $agency->email_lien_he,
                'so_dien_thoai' => $agency->so_dien_thoai,
                'dia_chi' => $agency->dia_chi,
                'cap_do' => $agency->cap_do,
                'cap_do_text' => $agency->getLevelName(),
                'trang_thai' => $agency->trang_thai,
                'trang_thai_text' => $agency->isActive() ? 'Hoạt động' : 'Không hoạt động',
                'so_phan_anh' => $agency->phanAnhs()->count(),
                'created_at' => $agency->created_at->format('d/m/Y H:i'),
            ];
        });

        return Inertia::render('admin/agencies/Index', [
            'agencies' => $agencies,
            'filters' => $request->only(['trang_thai', 'cap_do', 'search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show create agency form
     */
    public function create()
    {
        return Inertia::render('admin/agencies/Create');
    }

    /**
     * Store new agency
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_co_quan' => ['required', 'string', 'max:200'],
            'email_lien_he' => ['required', 'email', 'max:100', 'unique:co_quan_xu_lys,email_lien_he'],
            'so_dien_thoai' => ['nullable', 'string', 'max:15'],
            'dia_chi' => ['nullable', 'string', 'max:300'],
            'cap_do' => ['required', 'integer', 'in:0,1,2'],
            'mo_ta' => ['nullable', 'string', 'max:500'],
            'trang_thai' => ['required', 'integer', 'in:0,1'],
        ]);

        $agency = CoQuanXuLy::create($validated);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_CREATE,
            NhatKyHeThong::LOAI_CO_QUAN,
            $agency->id,
            ['ten_co_quan' => $agency->ten_co_quan]
        );

        return redirect()->route('admin.agencies.index')->with('success', 'Tạo cơ quan xử lý thành công!');
    }

    /**
     * Display agency details
     */
    public function show($id)
    {
        $agency = CoQuanXuLy::with(['phanAnhs'])->findOrFail($id);

        return Inertia::render('admin/agencies/Show', [
            'agency' => [
                'id' => $agency->id,
                'ten_co_quan' => $agency->ten_co_quan,
                'email_lien_he' => $agency->email_lien_he,
                'so_dien_thoai' => $agency->so_dien_thoai,
                'dia_chi' => $agency->dia_chi,
                'cap_do' => $agency->cap_do,
                'cap_do_text' => $agency->getLevelName(),
                'mo_ta' => $agency->mo_ta,
                'trang_thai' => $agency->trang_thai,
                'created_at' => $agency->created_at->format('d/m/Y H:i'),
                'updated_at' => $agency->updated_at->format('d/m/Y H:i'),
                'phan_anhs' => $agency->phanAnhs->map(function ($report) {
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
     * Show edit agency form
     */
    public function edit($id)
    {
        $agency = CoQuanXuLy::findOrFail($id);

        return Inertia::render('admin/agencies/Edit', [
            'agency' => [
                'id' => $agency->id,
                'ten_co_quan' => $agency->ten_co_quan,
                'email_lien_he' => $agency->email_lien_he,
                'so_dien_thoai' => $agency->so_dien_thoai,
                'dia_chi' => $agency->dia_chi,
                'cap_do' => $agency->cap_do,
                'mo_ta' => $agency->mo_ta,
                'trang_thai' => $agency->trang_thai,
            ],
        ]);
    }

    /**
     * Update agency
     */
    public function update(Request $request, $id)
    {
        $agency = CoQuanXuLy::findOrFail($id);

        $validated = $request->validate([
            'ten_co_quan' => ['required', 'string', 'max:200'],
            'email_lien_he' => ['required', 'email', 'max:100', 'unique:co_quan_xu_lys,email_lien_he,' . $id],
            'so_dien_thoai' => ['nullable', 'string', 'max:15'],
            'dia_chi' => ['nullable', 'string', 'max:300'],
            'cap_do' => ['required', 'integer', 'in:0,1,2'],
            'mo_ta' => ['nullable', 'string', 'max:500'],
            'trang_thai' => ['required', 'integer', 'in:0,1'],
        ]);

        $agency->update($validated);

        // Log activity
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_UPDATE,
            NhatKyHeThong::LOAI_CO_QUAN,
            $agency->id,
            ['ten_co_quan' => $agency->ten_co_quan]
        );

        return redirect()->route('admin.agencies.show', $agency->id)->with('success', 'Cập nhật cơ quan xử lý thành công!');
    }

    /**
     * Delete agency
     */
    public function destroy($id)
    {
        $agency = CoQuanXuLy::findOrFail($id);

        // Check if agency has reports
        if ($agency->phanAnhs()->count() > 0) {
            return redirect()->back()->with('error', 'Không thể xóa cơ quan đang có phản ánh!');
        }

        // Log before delete
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->id(),
            NhatKyHeThong::HANH_DONG_DELETE,
            NhatKyHeThong::LOAI_CO_QUAN,
            $agency->id,
            ['ten_co_quan' => $agency->ten_co_quan]
        );

        $agency->delete();

        return redirect()->route('admin.agencies.index')->with('success', 'Xóa cơ quan xử lý thành công!');
    }
}
