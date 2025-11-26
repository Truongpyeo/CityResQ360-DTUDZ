<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
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
            'ten_vai_tro' => ['required', 'string', 'max:100'],
            'slug'        => ['required', 'string', 'max:100', 'unique:vai_tros,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'mo_ta'       => ['nullable', 'string'],
            'trang_thai'  => ['required', 'integer', 'in:0,1'],
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
            'ten_vai_tro.required' => 'Tên vai trò là bắt buộc',
            'ten_vai_tro.max'      => 'Tên vai trò không được vượt quá :max ký tự',
            'slug.required'        => 'Slug là bắt buộc',
            'slug.unique'          => 'Slug đã tồn tại',
            'slug.regex'           => 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang',
            'trang_thai.required'  => 'Trạng thái là bắt buộc',
            'trang_thai.in'        => 'Trạng thái không hợp lệ',
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
            'ten_vai_tro' => 'Tên vai trò',
            'slug'        => 'Slug',
            'mo_ta'       => 'Mô tả',
            'trang_thai'  => 'Trạng thái',
        ];
    }
}
