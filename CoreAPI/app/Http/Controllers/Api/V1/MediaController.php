<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Media\UploadMediaRequest;
use App\Models\HinhAnhPhanAnh;
use App\Services\MediaServiceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaController extends BaseController
{
    protected MediaServiceClient $mediaService;

    public function __construct(MediaServiceClient $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Upload media (image/video)
     * POST /api/v1/media/upload
     *
     * Tries Media Service first, falls back to local storage if unavailable
     */
    public function upload(UploadMediaRequest $request)
    {
        $file = $request->file('file');
        $type = $request->type;
        $user = $request->user();

        // Get token from request for Media Service
        $token = $request->bearerToken();

        // Try Media Service first
        $mediaServiceResult = $this->mediaService->upload(
            $file,
            $type,
            $user->id,
            $request->lien_ket_den ?? 'phan_anh',
            $request->id_lien_ket ?? null,
            $request->mo_ta ?? null,
            $token
        );

        if ($mediaServiceResult !== null) {
            // Media Service succeeded - save reference to local database
            try {
                $media = HinhAnhPhanAnh::create([
                    'nguoi_dung_id' => $user->id,
                    'duong_dan_hinh_anh' => $mediaServiceResult['url'] ?? $mediaServiceResult['duong_dan'] ?? null,
                    'duong_dan_thumbnail' => $mediaServiceResult['thumbnail_url'] ?? $mediaServiceResult['duong_dan_thumbnail'] ?? null,
                    'loai_file' => $type,
                    'kich_thuoc' => $mediaServiceResult['kich_thuoc'] ?? $file->getSize(),
                    'dinh_dang' => $mediaServiceResult['dinh_dang'] ?? $file->getMimeType(),
                    'mo_ta' => $request->mo_ta ?? null,
                    // Store Media Service ID for future reference
                    'media_service_id' => $mediaServiceResult['id'] ?? null,
                ]);

                return $this->created([
                    'id' => $media->id,
                    'media_service_id' => $mediaServiceResult['id'] ?? null,
                    'url' => $mediaServiceResult['url'] ?? $mediaServiceResult['duong_dan'] ?? null,
                    'thumbnail_url' => $mediaServiceResult['thumbnail_url'] ?? $mediaServiceResult['duong_dan_thumbnail'] ?? null,
                    'type' => $type,
                    'kich_thuoc' => $media->kich_thuoc,
                    'dinh_dang' => $media->dinh_dang,
                    'created_at' => $media->created_at,
                ], 'Upload thành công');
            } catch (\Exception $e) {
                Log::warning('Failed to save media reference to local database', [
                    'error' => $e->getMessage(),
                    'media_service_result' => $mediaServiceResult,
                ]);

                // Still return success if Media Service worked
                return $this->created($mediaServiceResult, 'Upload thành công');
            }
        }

        // Fallback to local storage
        Log::info('Media Service unavailable, using local storage fallback');

        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;
        $path = 'media/'.$type.'s/'.date('Y/m');

        try {
            // Store original file
            $filePath = $file->storeAs($path, $filename, 'public');
            $fullUrl = Storage::url($filePath);

            // Generate thumbnail for images
            $thumbnailUrl = null;
            if ($type === 'image') {
                try {
                    $thumbnailPath = $path.'/thumb_'.$filename;

                    // Create thumbnail (300x300) using Intervention Image
                    $manager = new ImageManager(new Driver);
                    $image = $manager->read($file->getRealPath());
                    $image->scale(width: 300);

                    // Save thumbnail - save directly to file path
                    $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);
                    $image->toJpeg(85)->save($fullThumbnailPath);

                    $thumbnailUrl = Storage::url($thumbnailPath);
                } catch (\Exception $e) {
                    Log::warning('Failed to create thumbnail', ['error' => $e->getMessage()]);
                    // Continue without thumbnail
                }
            }

            // Save to database
            $media = HinhAnhPhanAnh::create([
                'nguoi_dung_id' => $user->id,
                'duong_dan_hinh_anh' => $fullUrl,
                'duong_dan_thumbnail' => $thumbnailUrl,
                'loai_file' => $type,
                'kich_thuoc' => $file->getSize(),
                'dinh_dang' => $file->getMimeType(),
                'mo_ta' => $request->get('mo_ta'),
            ]);

            return $this->created([
                'id' => $media->id,
                'url' => url($fullUrl),
                'thumbnail_url' => $thumbnailUrl ? url($thumbnailUrl) : null,
                'type' => $type,
                'kich_thuoc' => $media->kich_thuoc,
                'dinh_dang' => $media->dinh_dang,
                'created_at' => $media->created_at,
            ], 'Upload thành công (local storage)');

        } catch (\Exception $e) {
            return $this->serverError('Lỗi khi upload file: '.$e->getMessage());
        }
    }

    /**
     * Get media detail
     * GET /api/v1/media/{id}
     */
    public function show($id)
    {
        $media = HinhAnhPhanAnh::find($id);

        if (! $media) {
            return $this->notFound('Không tìm thấy file');
        }

        return $this->success([
            'id' => $media->id,
            'url' => url($media->duong_dan_hinh_anh),
            'thumbnail_url' => $media->duong_dan_thumbnail ? url($media->duong_dan_thumbnail) : null,
            'type' => $media->loai_file,
            'kich_thuoc' => $media->kich_thuoc,
            'dinh_dang' => $media->dinh_dang,
            'mo_ta' => $media->mo_ta,
            'created_at' => $media->created_at,
        ]);
    }

    /**
     * Delete media (only owner)
     * DELETE /api/v1/media/{id}
     */
    public function destroy(Request $request, $id)
    {
        $media = HinhAnhPhanAnh::find($id);

        if (! $media) {
            return $this->notFound('Không tìm thấy file');
        }

        // Check ownership
        if ($media->nguoi_dung_id !== $request->user()->id) {
            return $this->forbidden('Bạn không có quyền xóa file này');
        }

        try {
            // Try to delete from Media Service if media_service_id exists
            if (! empty($media->media_service_id)) {
                $token = $request->bearerToken();
                $deleted = $this->mediaService->delete($media->media_service_id, $request->user()->id, $token);
                if (! $deleted) {
                    Log::warning('Failed to delete from Media Service', [
                        'media_service_id' => $media->media_service_id,
                    ]);
                }
            }

            // Delete files from local storage (fallback or if stored locally)
            if ($media->duong_dan_hinh_anh && str_contains($media->duong_dan_hinh_anh, '/storage/')) {
                $path = str_replace('/storage/', '', parse_url($media->duong_dan_hinh_anh, PHP_URL_PATH));
                Storage::disk('public')->delete($path);
            }

            if ($media->duong_dan_thumbnail && str_contains($media->duong_dan_thumbnail, '/storage/')) {
                $path = str_replace('/storage/', '', parse_url($media->duong_dan_thumbnail, PHP_URL_PATH));
                Storage::disk('public')->delete($path);
            }

            // Delete from database
            $media->delete();

            return $this->success(null, 'Xóa file thành công');

        } catch (\Exception $e) {
            return $this->serverError('Lỗi khi xóa file: '.$e->getMessage());
        }
    }

    /**
     * List user's uploaded media
     * GET /api/v1/media/my
     */
    public function myMedia(Request $request)
    {
        $query = HinhAnhPhanAnh::where('nguoi_dung_id', $request->user()->id);

        // Filter by type
        if ($request->has('type')) {
            $query->where('loai_file', $request->type);
        }

        // Sort
        $query->orderBy('created_at', 'desc');

        // Paginate
        $perPage = $request->get('per_page', 20);
        $media = $query->paginate($perPage);

        // Transform data
        $data = $media->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'url' => url($item->duong_dan_hinh_anh),
                'thumbnail_url' => $item->duong_dan_thumbnail ? url($item->duong_dan_thumbnail) : null,
                'type' => $item->loai_file,
                'kich_thuoc' => $item->kich_thuoc,
                'dinh_dang' => $item->dinh_dang,
                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => $data,
            'meta' => [
                'current_page' => $media->currentPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
                'last_page' => $media->lastPage(),
            ],
        ]);
    }
}
