<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApartmentFilterRequest extends FormRequest
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
        'city_id'     => 'sometimes|exists:cities,id',
        'area_id'     => 'sometimes|exists:areas,id',
        'price_min'   => 'sometimes|numeric|min:0',
        'price_max'   => 'sometimes|numeric|min:0|gte:price_min',
        'rooms'       => 'sometimes|integer|min:1',
        'wifi'    => 'sometimes|boolean',
    ];
}
}
