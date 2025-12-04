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

namespace App\Http\Requests\Api\Media;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,mp4,mov,avi|max:20480', // 20MB
            'type' => 'required|in:image,video',
            'lien_ket_den' => 'required|in:phan_anh,binh_luan',
            'mo_ta' => 'sometimes|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File là bắt buộc',
            'file.mimes' => 'File phải là ảnh (jpeg, jpg, png, gif) hoặc video (mp4, mov, avi)',
            'file.max' => 'Kích thước file không được vượt quá 20MB',
            'type.required' => 'Loại file là bắt buộc',
            'type.in' => 'Loại file không hợp lệ',
            'lien_ket_den.required' => 'Liên kết đến là bắt buộc',
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
