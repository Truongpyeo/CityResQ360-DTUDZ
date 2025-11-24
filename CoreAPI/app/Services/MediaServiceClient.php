<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MediaServiceClient
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.media_service.url', env('MEDIA_SERVICE_URL', 'http://media-service:8004/api/v1'));
        $this->apiKey = config('services.media_service.api_key', env('MEDIA_SERVICE_API_KEY', ''));
    }

    /**
     * Upload media file to Media Service
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $type  image|video
     * @param  string|null  $lienKetDen  phan_anh|binh_luan
     * @param  string|null  $token  Bearer token from request
     */
    public function upload(
        $file,
        string $type,
        int $userId,
        ?string $lienKetDen = 'phan_anh',
        ?int $idLienKet = null,
        ?string $moTa = null,
        ?string $token = null
    ): ?array {
        try {
            $headers = [
                'X-User-Id' => (string) $userId,
            ];

            // Add Authorization header if token is provided
            if ($token) {
                $headers['Authorization'] = 'Bearer '.$token;
            }

            $response = Http::withHeaders($headers)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post("{$this->baseUrl}/media/upload", [
                    'type' => $type,
                    'lien_ket_den' => $lienKetDen,
                    'id_lien_ket' => $idLienKet,
                    'mo_ta' => $moTa,
                ]);

            if ($response->successful()) {
                return $response->json('data');
            }

            Log::error('Media Service upload failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Media Service upload exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get media by ID
     */
    public function get(string $mediaId, int $userId, ?string $token = null): ?array
    {
        try {
            $headers = [
                'X-User-Id' => (string) $userId,
            ];

            if ($token) {
                $headers['Authorization'] = 'Bearer '.$token;
            }

            $response = Http::withHeaders($headers)->get("{$this->baseUrl}/media/{$mediaId}");

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Media Service get exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete media
     */
    public function delete(string $mediaId, int $userId, ?string $token = null): bool
    {
        try {
            $headers = [
                'X-User-Id' => (string) $userId,
            ];

            if ($token) {
                $headers['Authorization'] = 'Bearer '.$token;
            }

            $response = Http::withHeaders($headers)->delete("{$this->baseUrl}/media/{$mediaId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Media Service delete exception', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * List user's media
     */
    public function list(int $userId, array $filters = [], ?string $token = null): ?array
    {
        try {
            $headers = [
                'X-User-Id' => (string) $userId,
            ];

            if ($token) {
                $headers['Authorization'] = 'Bearer '.$token;
            }

            $response = Http::withHeaders($headers)->get("{$this->baseUrl}/media/my", $filters);

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Media Service list exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
