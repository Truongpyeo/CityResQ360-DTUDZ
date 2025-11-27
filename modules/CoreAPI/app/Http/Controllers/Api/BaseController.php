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



namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * Success response
     *
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, string $message = 'Success', int $code = 200)
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * Error response
     *
     * @param  mixed  $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message = 'Error', $errors = null, int $code = 400)
    {
        return ApiResponse::error($message, $errors, $code);
    }

    /**
     * Validation error
     *
     * @param  mixed  $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationError($errors, string $message = 'Dữ liệu không hợp lệ')
    {
        return ApiResponse::validationError($errors, $message);
    }

    /**
     * Unauthorized
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorized(string $message = 'Chưa xác thực')
    {
        return ApiResponse::unauthorized($message);
    }

    /**
     * Forbidden
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbidden(string $message = 'Không có quyền truy cập')
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * Not found
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFound(string $message = 'Không tìm thấy')
    {
        return ApiResponse::notFound($message);
    }

    /**
     * Paginated response
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator  $paginator
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginated($paginator, string $message = 'Success')
    {
        return ApiResponse::paginated($paginator, $message);
    }

    /**
     * Created response (201)
     *
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function created($data = null, string $message = 'Tạo thành công')
    {
        return ApiResponse::created($data, $message);
    }

    /**
     * No content response (204)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function noContent()
    {
        return ApiResponse::noContent();
    }

    /**
     * Too many requests (429)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function tooManyRequests(string $message = 'Quá nhiều yêu cầu. Vui lòng thử lại sau')
    {
        return ApiResponse::tooManyRequests($message);
    }

    /**
     * Server error (500)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function serverError(string $message = 'Lỗi máy chủ. Vui lòng thử lại sau')
    {
        return ApiResponse::serverError($message);
    }
}
