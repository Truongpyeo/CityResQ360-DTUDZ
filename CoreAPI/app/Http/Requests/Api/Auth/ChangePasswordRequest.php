<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mat_khau_cu' => 'required|string',
            'mat_khau_moi' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'mat_khau_cu.required' => 'Mật khẩu cũ là bắt buộc',
            'mat_khau_moi.required' => 'Mật khẩu mới là bắt buộc',
            'mat_khau_moi.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự',
            'mat_khau_moi.confirmed' => 'Xác nhận mật khẩu mới không khớp',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
