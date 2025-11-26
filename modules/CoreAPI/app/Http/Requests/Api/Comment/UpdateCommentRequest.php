<?php

namespace App\Http\Requests\Api\Comment;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'noi_dung' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'noi_dung.required' => 'Nội dung bình luận là bắt buộc',
            'noi_dung.max' => 'Nội dung bình luận không được vượt quá 1000 ký tự',
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
