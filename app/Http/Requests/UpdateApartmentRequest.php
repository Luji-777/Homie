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
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'discription'=>'string',
            'address'=>'string|max:255',
            'price_per_day'=>'numeric|min:0',
            'price_per_month'=>'numeric|min:0',
            'bedrooms'=>'integer|min:0',
            'bathrooms'=>'integer|min:0',
            'wifi'=>'boolean',
            'garage'=>'boolean',
            'specifications'=>'string'
        ];
    }
}
