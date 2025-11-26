<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
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
        $adminId = $this->route('id');

        return [
            'ho_ten'        => ['required', 'string', 'max:100'],
            'email'         => ['required', 'string', 'email', 'max:100', 'unique:quan_tri_viens,email,' . $adminId],
            'so_dien_thoai' => ['nullable', 'string', 'max:15'],
            'id_vai_tro'    => ['required', 'exists:vai_tros,id'],
            'trang_thai'    => ['required', 'integer', 'in:0,1'],
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
            'ho_ten.required'     => 'Họ tên là bắt buộc',
            'ho_ten.max'          => 'Họ tên không được vượt quá :max ký tự',
            'email.required'      => 'Email là bắt buộc',
            'email.email'         => 'Email không hợp lệ',
            'email.unique'        => 'Email đã tồn tại',
            'so_dien_thoai.max'   => 'Số điện thoại không được vượt quá :max ký tự',
            'id_vai_tro.required' => 'Vai trò là bắt buộc',
            'id_vai_tro.exists'   => 'Vai trò không tồn tại',
            'trang_thai.required' => 'Trạng thái là bắt buộc',
            'trang_thai.in'       => 'Trạng thái không hợp lệ',
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
            'id_vai_tro'    => 'Vai trò',
            'trang_thai'    => 'Trạng thái',
        ];
    }
}
