<?php

namespace App\Http\Requests\Api\Report;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'danh_gia_hai_long' => 'required|integer|min:1|max:5',
            'nhan_xet' => 'sometimes|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'danh_gia_hai_long.required' => 'Đánh giá là bắt buộc',
            'danh_gia_hai_long.min' => 'Đánh giá phải từ 1 đến 5 sao',
            'danh_gia_hai_long.max' => 'Đánh giá phải từ 1 đến 5 sao',
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
