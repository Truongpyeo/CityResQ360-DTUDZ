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

namespace App\Http\Requests\Api\Report;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'required|string',
            'danh_muc_id' => 'required_without:danh_muc|exists:danh_muc_phan_anhs,id',
            'danh_muc' => 'sometimes|exists:danh_muc_phan_anhs,id',
            'uu_tien_id' => 'sometimes|exists:muc_uu_tiens,id',
            'uu_tien' => 'sometimes|exists:muc_uu_tiens,id',
            'vi_do' => 'required|numeric|between:-90,90',
            'kinh_do' => 'required|numeric|between:-180,180',
            'dia_chi' => 'required|string',
            'la_cong_khai' => 'sometimes|boolean',
            'the_tags' => 'sometimes|array',
            'media_ids' => 'sometimes|array',
        ];
    }

    public function messages(): array
    {
        return [
            'tieu_de.required' => 'Tiêu đề là bắt buộc',
            'mo_ta.required' => 'Mô tả là bắt buộc',
            'danh_muc.required' => 'Danh mục là bắt buộc',
            'vi_do.required' => 'Vị trí là bắt buộc',
            'kinh_do.required' => 'Vị trí là bắt buộc',
            'dia_chi.required' => 'Địa chỉ là bắt buộc',
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
