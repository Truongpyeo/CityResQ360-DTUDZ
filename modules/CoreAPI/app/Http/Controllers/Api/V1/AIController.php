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
use App\Services\AIMLServiceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AIController extends BaseController
{
    protected AIMLServiceClient $aimlService;

    public function __construct(AIMLServiceClient $aimlService)
    {
        $this->aimlService = $aimlService;
    }

    /**
     * Analyze image for incident type detection
     * POST /api/v1/ai/analyze
     */
    public function analyze(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,jpg,webp|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $file = $request->file('file');
            $token = $request->bearerToken();

            Log::info('AI analyze request', [
                'user_id' => $request->user()?->id,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);

            $result = $this->aimlService->analyze($file, $token);

            if ($result === null) {
                return $this->serverError('AIMLService không khả dụng hoặc phân tích thất bại');
            }

            return $this->success([
                'analysis' => $result,
            ], 'Phân tích AI thành công');
        } catch (\Exception $e) {
            Log::error('AI analyze error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverError('Lỗi khi phân tích AI: ' . $e->getMessage());
        }
    }

    /**
     * Analyze base64 image
     * POST /api/v1/ai/analyze-base64
     */
    public function analyzeBase64(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_base64' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $base64Image = $request->input('image_base64');
            $token = $request->bearerToken();

            Log::info('AI analyze-base64 request', [
                'user_id' => $request->user()?->id,
                'image_length' => strlen($base64Image),
            ]);

            $result = $this->aimlService->analyzeBase64($base64Image, $token);

            if ($result === null) {
                return $this->serverError('AIMLService không khả dụng hoặc phân tích thất bại');
            }

            return $this->success([
                'analysis' => $result,
            ], 'Phân tích AI thành công');
        } catch (\Exception $e) {
            Log::error('AI analyze-base64 error', [
                'error' => $e->getMessage(),
            ]);

            return $this->serverError('Lỗi khi phân tích AI: ' . $e->getMessage());
        }
    }

    /**
     * Analyze image and return format for CoreAPI Report
     * POST /api/v1/ai/analyze-for-report
     */
    public function analyzeForReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $file = $request->file('file');
            $token = $request->bearerToken();

            Log::info('AI analyze-for-report request', [
                'user_id' => $request->user()?->id,
                'file_name' => $file->getClientOriginalName(),
            ]);

            $result = $this->aimlService->analyzeForReport($file, $token);

            if ($result === null) {
                return $this->serverError('AIMLService không khả dụng hoặc phân tích thất bại');
            }

            return $this->success($result, 'Phân tích AI thành công');
        } catch (\Exception $e) {
            Log::error('AI analyze-for-report error', [
                'error' => $e->getMessage(),
            ]);

            return $this->serverError('Lỗi khi phân tích AI: ' . $e->getMessage());
        }
    }

    /**
     * Check AIMLService health
     * GET /api/v1/ai/health
     */
    public function health()
    {
        try {
            $isAvailable = $this->aimlService->isAvailable();

            return $this->success([
                'service' => 'AIMLService (via CoreAPI)',
                'status' => $isAvailable ? 'available' : 'unavailable',
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return $this->serverError('Lỗi khi kiểm tra AIMLService: ' . $e->getMessage());
        }
    }
}
