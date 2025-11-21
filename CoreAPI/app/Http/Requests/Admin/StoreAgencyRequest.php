<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgencyRequest extends FormRequest
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
            'ten_co_quan'   => ['required', 'string', 'max:200'],
            'email_lien_he' => ['required', 'email', 'max:100', 'unique:co_quan_xu_lys,email_lien_he'],
            'so_dien_thoai' => ['nullable', 'string', 'max:15'],
            'dia_chi'       => ['nullable', 'string', 'max:300'],
            'cap_do'        => ['required', 'integer', 'in:0,1,2'],
            'mo_ta'         => ['nullable', 'string', 'max:500'],
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
            'ten_co_quan.required'  => 'Tên cơ quan là bắt buộc',
            'ten_co_quan.max'       => 'Tên cơ quan không được vượt quá :max ký tự',
            'email_lien_he.required' => 'Email liên hệ là bắt buộc',
            'email_lien_he.email'   => 'Email liên hệ không hợp lệ',
            'email_lien_he.unique'  => 'Email liên hệ đã tồn tại',
            'so_dien_thoai.max'     => 'Số điện thoại không được vượt quá :max ký tự',
            'dia_chi.max'           => 'Địa chỉ không được vượt quá :max ký tự',
            'cap_do.required'       => 'Cấp độ là bắt buộc',
            'cap_do.in'             => 'Cấp độ không hợp lệ',
            'mo_ta.max'             => 'Mô tả không được vượt quá :max ký tự',
            'trang_thai.required'   => 'Trạng thái là bắt buộc',
            'trang_thai.in'         => 'Trạng thái không hợp lệ',
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
            'ten_co_quan'   => 'Tên cơ quan',
            'email_lien_he' => 'Email liên hệ',
            'so_dien_thoai' => 'Số điện thoại',
            'dia_chi'       => 'Địa chỉ',
            'cap_do'        => 'Cấp độ',
            'mo_ta'         => 'Mô tả',
            'trang_thai'    => 'Trạng thái',
        ];
    }
}
