<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'ho_ten' => 'sometimes|string|max:255',
            'so_dien_thoai' => "sometimes|string|max:15|unique:nguoi_dungs,so_dien_thoai,{$userId}",
            'anh_dai_dien' => 'sometimes|string',
        ];
    }

    public function messages(): array
    {
        return [
            'ho_ten.string' => 'Họ tên phải là chuỗi ký tự',
            'ho_ten.max' => 'Họ tên không được vượt quá 255 ký tự',
            'so_dien_thoai.unique' => 'Số điện thoại đã được sử dụng',
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
