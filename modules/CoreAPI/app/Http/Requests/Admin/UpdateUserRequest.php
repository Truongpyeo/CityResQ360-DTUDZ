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

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id');

        return [
            'ho_ten'        => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:nguoi_dungs,email,' . $userId],
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
