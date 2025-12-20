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
            'type'=>[
                'required',
                Rule::in(['room',
                'studio',
                'house',
                'villa'])
            ],
            'discription'=>'required|string',
            'city'=>'required|string|max:255',
            'address'=>'required|string|max:255',
            'price_per_day'=>'required|numeric|min:0',
            'price_per_month'=>'required|numeric|min:0',
            'bedrooms'=>'required|integer|min:0',
            'bathrooms'=>'required|integer|min:0',
            'wifi'=>'required|boolean',
            'garage'=>'required|boolean',
            'specifications'=>'string'
        ];
    }
}
