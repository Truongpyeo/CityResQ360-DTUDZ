<?php

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
