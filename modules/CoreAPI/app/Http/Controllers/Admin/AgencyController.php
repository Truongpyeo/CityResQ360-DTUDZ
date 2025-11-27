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



namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAgencyRequest;
use App\Http\Requests\Admin\UpdateAgencyRequest;
use App\Models\CoQuanXuLy;
use App\Models\NhatKyHeThong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
    public function store(StoreAgencyRequest $request)
    {
        $agency = CoQuanXuLy::create($request->validated());

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
    public function update(UpdateAgencyRequest $request, $id)
    {
        $agency = CoQuanXuLy::findOrFail($id);

        $agency->update($request->validated());

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

        // Authorization check
        if (Gate::forUser(auth()->guard('admin')->user())->denies('delete', $agency)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa cơ quan này!');
        }

        // Check if agency has reports (policy also checks this)
        if ($agency->phanAnhs()->count() > 0) {
            return redirect()->back()->with('error', 'Không thể xóa cơ quan đang có phản ánh!');
        }

        // Log before delete
        NhatKyHeThong::logActivity(
            auth()->guard('admin')->user()->id,
            NhatKyHeThong::HANH_DONG_DELETE,
            NhatKyHeThong::LOAI_CO_QUAN,
            $agency->id,
            ['ten_co_quan' => $agency->ten_co_quan]
        );

        $agency->delete();

        return redirect()->route('admin.agencies.index')->with('success', 'Xóa cơ quan xử lý thành công!');
    }

    /**
     * Export agencies to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search']);

        $filename = 'co-quan-xu-ly-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AgenciesExport($filters),
            $filename
        );
    }
}
