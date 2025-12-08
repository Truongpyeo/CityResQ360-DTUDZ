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

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIMLServiceClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.aiml_service.url', env('AIML_SERVICE_URL', 'http://aiml-service:8003'));
    }

    /**
     * Analyze image using AI/ML Service
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string|null  $token  Bearer token from request
     * @return array|null AI analysis result or null on failure
     */
    public function analyzeForReport($file, ?string $token = null): ?array
    {
        try {
            $headers = [];

            // Add Authorization header if token is provided
            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }

            Log::info('Sending image to AI/ML Service for analysis', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'aiml_url' => $this->baseUrl,
            ]);

            $response = Http::withHeaders($headers)
                ->timeout(30) // AI analysis may take time
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post("{$this->baseUrl}/analyze-for-report");

            if ($response->successful()) {
                $result = $response->json();

                Log::info('AI/ML Service analysis successful', [
                    'label' => $result['data']['ai_analysis']['label'] ?? 'unknown',
                    'confidence' => $result['data']['ai_analysis']['confidence'] ?? 0,
                ]);

                return $result['data'] ?? null;
            }

            Log::warning('AI/ML Service analysis failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('AI/ML Service analysis exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Analyze image (public API - optional auth)
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string|null  $token  Bearer token (optional)
     * @return array|null AI analysis result
     */
    public function analyze($file, ?string $token = null): ?array
    {
        try {
            $headers = [];

            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post("{$this->baseUrl}/analyze");

            if ($response->successful()) {
                return $response->json()['analysis'] ?? null;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('AI/ML Service analyze exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Analyze base64 image
     *
     * @param  string  $base64Image
     * @param  string|null  $token
     * @return array|null
     */
    public function analyzeBase64(string $base64Image, ?string $token = null): ?array
    {
        try {
            $headers = [];

            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post("{$this->baseUrl}/analyze-base64", [
                    'image_base64' => $base64Image,
                ]);

            if ($response->successful()) {
                return $response->json()['analysis'] ?? null;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('AI/ML Service analyze-base64 exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if AI/ML Service is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");

            return $response->successful() &&
                   ($response->json()['status'] === 'healthy' || $response->json()['status'] === 'ok');
        } catch (\Exception $e) {
            return false;
        }
    }
}
