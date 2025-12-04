<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
        $roleId = $this->route('id');

        return [
            'ten_vai_tro' => ['required', 'string', 'max:100'],
            'slug'        => ['required', 'string', 'max:100', 'unique:vai_tros,slug,' . $roleId, 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
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
