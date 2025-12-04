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
