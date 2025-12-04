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

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DanhMucPhanAnh;
use App\Models\MucUuTien;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Get all categories
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = DanhMucPhanAnh::select('id', 'ten_danh_muc', 'icon', 'mau_sac', 'thu_tu_hien_thi', 'mo_ta')
            ->where('trang_thai', true)
            ->orderBy('thu_tu_hien_thi')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get all priorities
     *
     * @return JsonResponse
     */
    public function priorities(): JsonResponse
    {
        $priorities = MucUuTien::select('id', 'ten_muc', 'cap_do', 'mau_sac', 'mo_ta')
            ->where('trang_thai', true)
            ->orderBy('cap_do')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $priorities
        ]);
    }

    /**
     * Get category detail
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $category = DanhMucPhanAnh::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Danh mục không tồn tại'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }
}
