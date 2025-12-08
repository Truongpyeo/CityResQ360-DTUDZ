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

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Media\UploadMediaRequest;
use App\Models\HinhAnhPhanAnh;
use App\Services\MediaServiceClient;
use App\Services\AIMLServiceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaController extends BaseController
{
    protected MediaServiceClient $mediaService;
    protected AIMLServiceClient $aimlService;

    public function __construct(MediaServiceClient $mediaService, AIMLServiceClient $aimlService)
    {
        $this->mediaService = $mediaService;
        $this->aimlService = $aimlService;
    }

    /**
     * Upload media (image/video)
     * POST /api/v1/media/upload
     *
     * Tries Media Service first, falls back to local storage if unavailable
     * For images: automatically sends to AI/ML Service for analysis
     */
    public function upload(UploadMediaRequest $request)
    {
        $file = $request->file('file');
        $type = $request->type;
        $user = $request->user();

        // Get token from request for Media Service
        $token = $request->bearerToken();

        // AI Analysis for images (async, non-blocking)
        $aiAnalysis = null;
        if ($type === 'image') {
            try {
                Log::info('Attempting AI analysis for uploaded image', [
                    'user_id' => $user->id,
                    'file_name' => $file->getClientOriginalName(),
                ]);

                $aiAnalysis = $this->aimlService->analyzeForReport($file, $token);

                if ($aiAnalysis) {
                    Log::info('AI analysis completed', [
                        'label' => $aiAnalysis['ai_analysis']['label'] ?? 'unknown',
                        'confidence' => $aiAnalysis['ai_analysis']['confidence'] ?? 0,
                        'category_id' => $aiAnalysis['danh_muc_id'] ?? null,
                    ]);
                } else {
                    Log::warning('AI analysis returned null - service may be unavailable');
                }
            } catch (\Exception $e) {
                Log::warning('AI analysis failed, continuing with upload', [
                    'error' => $e->getMessage(),
                ]);
                // Continue with upload even if AI fails
            }
        }

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

                $response = [
                    'id' => $media->id,
                    'media_service_id' => $mediaServiceResult['id'] ?? null,
                    'url' => $this->getFullMediaUrl($mediaServiceResult['url'] ?? $mediaServiceResult['duong_dan'] ?? null),
                    'thumbnail_url' => $this->getFullMediaUrl($mediaServiceResult['thumbnail_url'] ?? $mediaServiceResult['duong_dan_thumbnail'] ?? null),
                    'type' => $type,
                    'kich_thuoc' => $media->kich_thuoc,
                    'dinh_dang' => $media->dinh_dang,
                    'created_at' => $media->created_at,
                ];

                // Add AI analysis to response if available
                if ($aiAnalysis) {
                    $response['ai_analysis'] = $aiAnalysis;
                }

                return $this->created($response, 'Upload thành công' . ($aiAnalysis ? ' (đã phân tích AI)' : ''));
            } catch (\Exception $e) {
                Log::warning('Failed to save media reference to local database', [
                    'error' => $e->getMessage(),
                    'media_service_result' => $mediaServiceResult,
                ]);

                // Still return success if Media Service worked
                $fallbackResponse = $mediaServiceResult;
                if ($aiAnalysis) {
                    $fallbackResponse['ai_analysis'] = $aiAnalysis;
                }

                return $this->created($fallbackResponse, 'Upload thành công');
            }
        }

        // Fallback to local storage
        Log::info('Media Service unavailable, using local storage fallback', [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'storage_disk' => config('filesystems.default'),
        ]);

        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = 'media/' . $type . 's/' . date('Y/m');

        try {
            // Use S3/MinIO for fallback storage
            $disk = config('filesystems.default', 's3');

            // Store original file to S3/MinIO
            $filePath = $file->storeAs($path, $filename, $disk);

            // Construct public URL directly using MinIO bucket path
            $publicBaseUrl = config('services.media_service.url', env('MEDIA_SERVICE_URL', 'https://media.cityresq360.io.vn'));
            $bucket = config('filesystems.disks.s3.bucket', 'cityresq-media');
            $fullUrl = rtrim($publicBaseUrl, '/') . '/' . $bucket . '/' . $filePath;

            Log::info('File stored successfully to S3/MinIO fallback', [
                'file_path' => $filePath,
                'full_url' => $fullUrl,
                'disk' => $disk,
            ]);

            // Generate thumbnail for images
            $thumbnailUrl = null;
            if ($type === 'image') {
                try {
                    $thumbnailFilename = 'thumb_' . $filename;
                    $thumbnailPath = $path . '/' . $thumbnailFilename;

                    // Create thumbnail (300x300) using Intervention Image
                    $manager = new ImageManager(new Driver);
                    $image = $manager->read($file->getRealPath());
                    $image->scale(width: 300);

                    // Save thumbnail to temp file first
                    $tempThumbPath = sys_get_temp_dir() . '/' . $thumbnailFilename;
                    $image->toJpeg(85)->save($tempThumbPath);

                    // Upload thumbnail to S3/MinIO
                    Storage::disk($disk)->put($thumbnailPath, file_get_contents($tempThumbPath));
                    $thumbnailUrl = rtrim($publicBaseUrl, '/') . '/' . $bucket . '/' . $thumbnailPath;

                    // Clean up temp file
                    @unlink($tempThumbPath);

                    Log::info('Thumbnail created and uploaded', ['thumbnail_url' => $thumbnailUrl]);
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

            $response = [
                'id' => $media->id,
                'url' => $this->getFullMediaUrl($fullUrl),
                'thumbnail_url' => $this->getFullMediaUrl($thumbnailUrl),
                'type' => $type,
                'kich_thuoc' => $media->kich_thuoc,
                'dinh_dang' => $media->dinh_dang,
                'created_at' => $media->created_at,
            ];

            // Add AI analysis to fallback response if available
            if ($aiAnalysis) {
                $response['ai_analysis'] = $aiAnalysis;
            }

            return $this->created($response, 'Upload thành công (S3/MinIO fallback)' . ($aiAnalysis ? ' (đã phân tích AI)' : ''));
        } catch (\Exception $e) {
            Log::error('Local storage upload failed', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'file_info' => [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'extension' => $extension,
                ],
                'storage_config' => [
                    'disk' => config('filesystems.default'),
                    'public_path' => Storage::disk('public')->path(''),
                ],
            ]);

            return $this->serverError('Lỗi khi upload file: ' . $e->getMessage());
        }
    }

    /**
     * Get media detail
     * GET /api/v1/media/{id}
     */
    public function show($id)
    {
        $media = HinhAnhPhanAnh::find($id);

        if (!$media) {
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

        if (!$media) {
            return $this->notFound('Không tìm thấy file');
        }

        // Check ownership
        if ($media->nguoi_dung_id !== $request->user()->id) {
            return $this->forbidden('Bạn không có quyền xóa file này');
        }

        try {
            // Try to delete from Media Service if media_service_id exists
            if (!empty($media->media_service_id)) {
                $token = $request->bearerToken();
                $deleted = $this->mediaService->delete($media->media_service_id, $request->user()->id, $token);
                if (!$deleted) {
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

            return $this->success([
                'id' => $id,
                'deleted' => true,
            ], 'Xóa file thành công');
        } catch (\Exception $e) {
            return $this->serverError('Lỗi khi xóa file: ' . $e->getMessage());
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

    /**
     * Convert relative media URL to full URL
     * Handles both MinIO internal URLs and relative paths
     * @param string|null $path
     * @return string|null
     */
    private function getFullMediaUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        Log::debug('getFullMediaUrl called', ['input' => $path]);

        // If already a full URL, check if it's internal service URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $publicUrl = config('services.media_service.url', env('MEDIA_SERVICE_URL', 'https://media.cityresq360.io.vn'));
            $publicUrl = rtrim($publicUrl, '/');

            // Check for MediaService internal URL pattern
            // Example: http://media-service:8004/api/v1/storage/media/images/2025/12/xxx.jpg 
            //       → https://media.cityresq360.io.vn/cityresq-media/2025/12/xxx.jpg
            if (preg_match('#https?://media-service:\d+/api/v\d+/storage/media/images/(\d{4})/(\d{2})/([^/]+)$#', $path, $matches)) {
                // Convert to MinIO bucket path: /cityresq-media/YYYY/MM/DD/filename
                $converted = $publicUrl . '/cityresq-media/' . $matches[1] . '/' . $matches[2] . '/' . date('d') . '/' . $matches[3];
                Log::debug('MediaService URL converted', ['from' => $path, 'to' => $converted]);
                return $converted;
            }

            // Check for MinIO internal URL pattern
            // Example: http://minio:9000/cityresq-media/storage/... → https://media.cityresq360.io.vn/storage/...
            $minioInternal = config('filesystems.disks.s3.endpoint', env('AWS_ENDPOINT', 'http://minio:9000'));
            if (str_starts_with($path, $minioInternal)) {
                // Extract path after bucket name
                $pattern = '#' . preg_quote($minioInternal, '#') . '/[^/]+/(.+)$#';
                if (preg_match($pattern, $path, $matches)) {
                    $converted = $publicUrl . '/' . $matches[1];
                    Log::debug('MinIO URL converted', ['from' => $path, 'to' => $converted]);
                    return $converted;
                }
            }

            Log::debug('URL returned as-is (not internal)', ['url' => $path]);
            return $path;
        }

        // Get Media Service base URL for relative paths
        $mediaServiceUrl = config('services.media_service.url', env('MEDIA_SERVICE_URL', 'https://media.cityresq360.io.vn'));
        $mediaServiceUrl = rtrim($mediaServiceUrl, '/');

        // Ensure path starts with /
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $result = $mediaServiceUrl . $path;
        Log::debug('Relative path converted', ['from' => $path, 'to' => $result]);
        return $result;
    }
}
