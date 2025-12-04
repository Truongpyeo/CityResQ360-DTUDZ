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

class StoreFunctionRequest extends FormRequest
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
            'ten_chuc_nang'  => ['required', 'string', 'max:100'],
            'route_name'     => ['required', 'string', 'max:150', 'unique:chuc_nangs,route_name'],
            'nhom_chuc_nang' => ['required', 'string', 'max:50'],
            'mo_ta'          => ['nullable', 'string'],
            'trang_thai'     => ['required', 'integer', 'in:0,1'],
            'thu_tu'         => ['nullable', 'integer', 'min:0'],
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
            'ten_chuc_nang.required'  => 'Tên chức năng là bắt buộc',
            'ten_chuc_nang.max'       => 'Tên chức năng không được vượt quá :max ký tự',
            'route_name.required'     => 'Route name là bắt buộc',
            'route_name.unique'       => 'Route name đã tồn tại',
            'nhom_chuc_nang.required' => 'Nhóm chức năng là bắt buộc',
            'nhom_chuc_nang.max'      => 'Nhóm chức năng không được vượt quá :max ký tự',
            'trang_thai.required'     => 'Trạng thái là bắt buộc',
            'trang_thai.in'           => 'Trạng thái không hợp lệ',
            'thu_tu.integer'          => 'Thứ tự phải là số nguyên',
            'thu_tu.min'              => 'Thứ tự phải lớn hơn hoặc bằng :min',
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
            'ten_chuc_nang'  => 'Tên chức năng',
            'route_name'     => 'Route name',
            'nhom_chuc_nang' => 'Nhóm chức năng',
            'mo_ta'          => 'Mô tả',
            'trang_thai'     => 'Trạng thái',
            'thu_tu'         => 'Thứ tự',
        ];
    }
}
