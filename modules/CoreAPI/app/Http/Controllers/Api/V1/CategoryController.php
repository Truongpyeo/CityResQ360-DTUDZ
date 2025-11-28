<?php

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
        $categories = DanhMucPhanAnh::select('id', 'ten', 'icon', 'mau_sac', 'thu_tu_hien_thi', 'mo_ta')
            ->where('is_active', true)
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
        $priorities = MucUuTien::select('id', 'ten', 'cap_do', 'mau_sac', 'mo_ta')
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
