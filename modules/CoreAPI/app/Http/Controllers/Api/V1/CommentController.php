<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Comment\StoreCommentRequest;
use App\Http\Requests\Api\Comment\UpdateCommentRequest;
use App\Models\BinhLuanPhanAnh;
use App\Models\PhanAnh;
use Illuminate\Http\Request;

class CommentController extends BaseController
{
    /**
     * List comments for a report
     * GET /api/v1/reports/{id}/comments
     */
    public function index(Request $request, $reportId)
    {
        $report = PhanAnh::find($reportId);

        if (! $report) {
            return $this->notFound('Không tìm thấy phản ánh');
        }

        $query = BinhLuanPhanAnh::with(['nguoiDung'])
            ->where('phan_anh_id', $reportId);

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 20);
        $comments = $query->paginate($perPage);

        // Transform data
        $data = $comments->getCollection()->map(function ($comment) use ($request) {
            // Check if current user liked this comment
            $userLiked = false;
            if ($request->user()) {
                // TODO: Check like status from likes table
                // For now, just return false
                $userLiked = false;
            }

            return [
                'id' => $comment->id,
                'noi_dung' => $comment->noi_dung,
                'user' => [
                    'id' => $comment->nguoiDung->id,
                    'ho_ten' => $comment->nguoiDung->ho_ten,
                    'anh_dai_dien' => $comment->nguoiDung->anh_dai_dien,
                ],
                'luot_thich' => $comment->luot_thich ?? 0,
                'user_liked' => $userLiked,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => $data,
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ],
        ]);
    }

    /**
     * Add comment to a report
     * POST /api/v1/reports/{id}/comments
     */
    public function store(StoreCommentRequest $request, $reportId)
    {
        $report = PhanAnh::find($reportId);

        if (! $report) {
            return $this->notFound('Không tìm thấy phản ánh');
        }

        $comment = BinhLuanPhanAnh::create([
            'phan_anh_id' => $reportId,
            'nguoi_dung_id' => $request->user()->id,
            'noi_dung' => $request->noi_dung,
            'luot_thich' => 0,
        ]);

        $comment->load('nguoiDung');

        return $this->created([
            'id' => $comment->id,
            'noi_dung' => $comment->noi_dung,
            'user' => [
                'id' => $comment->nguoiDung->id,
                'ho_ten' => $comment->nguoiDung->ho_ten,
                'anh_dai_dien' => $comment->nguoiDung->anh_dai_dien,
            ],
            'luot_thich' => $comment->luot_thich,
            'user_liked' => false,
            'created_at' => $comment->created_at,
        ], 'Thêm bình luận thành công');
    }

    /**
     * Update comment (only author)
     * PUT /api/v1/comments/{id}
     */
    public function update(UpdateCommentRequest $request, $id)
    {
        $comment = BinhLuanPhanAnh::find($id);

        if (! $comment) {
            return $this->notFound('Không tìm thấy bình luận');
        }

        // Check ownership
        if ($comment->nguoi_dung_id !== $request->user()->id) {
            return $this->forbidden('Bạn không có quyền chỉnh sửa bình luận này');
        }

        $comment->update([
            'noi_dung' => $request->noi_dung,
        ]);

        return $this->success([
            'id' => $comment->id,
            'noi_dung' => $comment->noi_dung,
            'updated_at' => $comment->updated_at,
        ], 'Cập nhật bình luận thành công');
    }

    /**
     * Delete comment (only author)
     * DELETE /api/v1/comments/{id}
     */
    public function destroy(Request $request, $id)
    {
        $comment = BinhLuanPhanAnh::find($id);

        if (! $comment) {
            return $this->notFound('Không tìm thấy bình luận');
        }

        // Check ownership
        if ($comment->nguoi_dung_id !== $request->user()->id) {
            return $this->forbidden('Bạn không có quyền xóa bình luận này');
        }

        $comment->delete();

        return $this->success(null, 'Xóa bình luận thành công');
    }

    /**
     * Like a comment
     * POST /api/v1/comments/{id}/like
     */
    public function like(Request $request, $id)
    {
        $comment = BinhLuanPhanAnh::find($id);

        if (! $comment) {
            return $this->notFound('Không tìm thấy bình luận');
        }

        // TODO: Check if user already liked (from likes table)
        // For now, just increment

        $comment->increment('luot_thich');

        return $this->success([
            'luot_thich' => $comment->luot_thich,
            'user_liked' => true,
        ], 'Thích bình luận thành công');
    }

    /**
     * Unlike a comment
     * DELETE /api/v1/comments/{id}/like
     */
    public function unlike(Request $request, $id)
    {
        $comment = BinhLuanPhanAnh::find($id);

        if (! $comment) {
            return $this->notFound('Không tìm thấy bình luận');
        }

        // TODO: Check if user actually liked it
        // For now, just decrement

        if ($comment->luot_thich > 0) {
            $comment->decrement('luot_thich');
        }

        return $this->success([
            'luot_thich' => $comment->luot_thich,
            'user_liked' => false,
        ], 'Bỏ thích bình luận thành công');
    }
}
