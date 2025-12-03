<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
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
            'tieu_de' => ['required', 'string', 'max:255'],
            'mo_ta' => ['required', 'string'],
            'danh_muc_id' => ['nullable', 'exists:danh_muc_phan_anhs,id'],
            'danh_muc' => ['nullable', 'exists:danh_muc_phan_anhs,id'],
            'uu_tien_id' => ['nullable', 'exists:muc_uu_tiens,id'],
            'uu_tien' => ['nullable', 'exists:muc_uu_tiens,id'],
            'vi_do' => ['required', 'numeric', 'between:-90,90'],
            'kinh_do' => ['required', 'numeric', 'between:-180,180'],
            'dia_chi' => ['required', 'string', 'max:500'],
            'la_cong_khai' => ['boolean'],
            'the_tags' => ['array'],
            'the_tags.*' => ['string', 'max:50'],
            'media_ids' => ['array'],
            'media_ids.*' => ['integer', 'exists:hinh_anh_phan_anhs,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'tieu_de.required' => 'Tiêu đề không được để trống',
            'mo_ta.required' => 'Mô tả không được để trống',
            'vi_do.required' => 'Vĩ độ không được để trống',
            'kinh_do.required' => 'Kinh độ không được để trống',
            'dia_chi.required' => 'Địa chỉ không được để trống',
        ];
    }
}
