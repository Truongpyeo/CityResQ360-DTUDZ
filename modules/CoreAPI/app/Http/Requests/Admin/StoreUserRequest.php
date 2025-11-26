<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'ho_ten'        => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:nguoi_dungs,email'],
            'so_dien_thoai' => ['required', 'string', 'max:20'],
            'vai_tro'       => ['required', 'integer', 'in:0,1'],
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
            'ho_ten.required'        => 'Họ tên là bắt buộc',
            'ho_ten.max'             => 'Họ tên không được vượt quá :max ký tự',
            'email.required'         => 'Email là bắt buộc',
            'email.email'            => 'Email không hợp lệ',
            'email.unique'           => 'Email đã tồn tại',
            'so_dien_thoai.required' => 'Số điện thoại là bắt buộc',
            'so_dien_thoai.max'      => 'Số điện thoại không được vượt quá :max ký tự',
            'vai_tro.required'       => 'Vai trò là bắt buộc',
            'vai_tro.in'             => 'Vai trò không hợp lệ',
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
            'ho_ten'        => 'Họ tên',
            'email'         => 'Email',
            'so_dien_thoai' => 'Số điện thoại',
            'vai_tro'       => 'Vai trò',
        ];
    }
}
