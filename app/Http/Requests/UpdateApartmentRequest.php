<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApartmentRequest extends FormRequest
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
            // 'area_id' => 'integer|exists:areas,id',

            'discription' => 'string',
            'address' => 'string|max:255',
            'price_per_day' => 'numeric|min:0',
            'price_per_month' => 'numeric|min:0',
            'bedrooms' => 'integer|min:0',
            'bathrooms' => 'integer|min:0',
            'wifi' => 'boolean',
            'garage' => 'boolean',
            'specifications' => 'string', //غالبا نكبو ما بدنا ياه

            //الصور
            'images' => 'array|min:1', // على الأقل صورة واحدة عند الإنشاء
            'images.*' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover_index' => 'integer|min:0' // رقم الصورة اللي بدنا نعملها غلاف (مثلاً 0 = الأولى) من مصفوفة الصور
        ];
    }
}
