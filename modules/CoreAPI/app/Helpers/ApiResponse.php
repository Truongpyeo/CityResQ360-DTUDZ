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

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Success response
     *
     * @param  mixed  $data
     */
    public static function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Error response
     *
     * @param  mixed  $errors
     */
    public static function error(string $message = 'Error', $errors = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Validation error response
     *
     * @param  mixed  $errors
     */
    public static function validationError($errors, string $message = 'Dữ liệu không hợp lệ'): JsonResponse
    {
        return self::error($message, $errors, 422);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Chưa xác thực'): JsonResponse
    {
        return self::error($message, null, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Không có quyền truy cập'): JsonResponse
    {
        return self::error($message, null, 403);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Không tìm thấy'): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * Paginated response
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator  $paginator
     */
    public static function paginated($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    /**
     * Created response (201)
     *
     * @param  mixed  $data
     */
    public static function created($data = null, string $message = 'Tạo thành công'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * No content response (204)
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Too many requests response (429)
     */
    public static function tooManyRequests(string $message = 'Quá nhiều yêu cầu. Vui lòng thử lại sau'): JsonResponse
    {
        return self::error($message, null, 429);
    }

    /**
     * Server error response (500)
     */
    public static function serverError(string $message = 'Lỗi máy chủ. Vui lòng thử lại sau'): JsonResponse
    {
        return self::error($message, null, 500);
    }
}
