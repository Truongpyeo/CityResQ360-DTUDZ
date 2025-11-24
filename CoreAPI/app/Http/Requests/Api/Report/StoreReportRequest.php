<?php

namespace App\Http\Requests\Api\Report;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'required|string',
            'danh_muc' => 'required|integer|min:0|max:10',
            'uu_tien' => 'sometimes|integer|min:0|max:3',
            'vi_do' => 'required|numeric|between:-90,90',
            'kinh_do' => 'required|numeric|between:-180,180',
            'dia_chi' => 'required|string',
            'la_cong_khai' => 'sometimes|boolean',
            'the_tags' => 'sometimes|array',
            'media_ids' => 'sometimes|array',
        ];
    }

    public function messages(): array
    {
        return [
            'tieu_de.required' => 'Tiêu đề là bắt buộc',
            'mo_ta.required' => 'Mô tả là bắt buộc',
            'danh_muc.required' => 'Danh mục là bắt buộc',
            'vi_do.required' => 'Vị trí là bắt buộc',
            'kinh_do.required' => 'Vị trí là bắt buộc',
            'dia_chi.required' => 'Địa chỉ là bắt buộc',
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
