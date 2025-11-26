<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddUserPointsRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'diem'  => ['required', 'integer', 'min:1', 'max:1000'],
            'ly_do' => ['required', 'string', 'max:200'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'diem.required'  => 'Số điểm là bắt buộc',
            'diem.integer'   => 'Số điểm phải là số nguyên',
            'diem.min'       => 'Số điểm phải ít nhất :min',
            'diem.max'       => 'Số điểm không được vượt quá :max',
            'ly_do.required' => 'Lý do là bắt buộc',
            'ly_do.max'      => 'Lý do không được vượt quá :max ký tự',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'diem'  => 'Số điểm',
            'ly_do' => 'Lý do',
        ];
    }
}
