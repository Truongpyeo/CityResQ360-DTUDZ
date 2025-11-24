<?php

namespace App\Http\Requests\Api\Vote;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loai_binh_chon' => 'required|integer|in:1,-1',
        ];
    }

    public function messages(): array
    {
        return [
            'loai_binh_chon.required' => 'Loại bình chọn là bắt buộc',
            'loai_binh_chon.in' => 'Loại bình chọn không hợp lệ (1: upvote, -1: downvote)',
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
