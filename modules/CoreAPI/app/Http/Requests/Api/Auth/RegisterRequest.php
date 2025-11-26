<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:nguoi_dungs,email',
            'mat_khau' => 'required|string|min:6|confirmed',
            'so_dien_thoai' => 'required|string|max:15|unique:nguoi_dungs,so_dien_thoai',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ho_ten.required' => 'Họ tên là bắt buộc',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã được sử dụng',
            'mat_khau.required' => 'Mật khẩu là bắt buộc',
            'mat_khau.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'mat_khau.confirmed' => 'Xác nhận mật khẩu không khớp',
            'so_dien_thoai.required' => 'Số điện thoại là bắt buộc',
            'so_dien_thoai.unique' => 'Số điện thoại đã được sử dụng',
        ];
    }

    /**
     * Handle a failed validation attempt (API response).
     */
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
