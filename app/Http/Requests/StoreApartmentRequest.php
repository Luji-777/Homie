<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApartmentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in([
                    'room',
                    'studio',
                    'house',
                    'villa'
                ])
            ],
            'area_id' => 'required|exists:areas,id',

            'discription' => 'required|string',

            'address' => 'required|string|max:255',
            'price_per_day' => 'required|numeric|min:0',
            'price_per_month' => 'required|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'wifi' => 'required|boolean',
            'garage' => 'required|boolean',
            'specifications' => 'string', //غالبا نكبو ما بدنا ياه

            //الصور
            'images' => 'required|array|min:1', // على الأقل صورة واحدة عند الإنشاء
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover_index' => 'required|integer|min:0' // رقم الصورة اللي بدنا نعملها غلاف (مثلاً 0 = الأولى) من مصفوفة الصور
        ];
    }
}
