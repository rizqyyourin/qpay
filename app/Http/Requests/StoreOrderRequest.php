<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'discount_amount' => 'nullable|numeric|min:0|decimal:0,2',
            'tax_amount' => 'nullable|numeric|min:0|decimal:0,2',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'discount_amount.numeric' => 'Discount amount must be a valid number',
            'tax_amount.numeric' => 'Tax amount must be a valid number',
        ];
    }
}
