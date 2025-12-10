<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'barcode' => 'nullable|string|unique:products,barcode',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|decimal:0,2',
            'cost' => 'nullable|numeric|min:0|decimal:0,2',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Category is required',
            'category_id.exists' => 'Selected category does not exist',
            'name.required' => 'Product name is required',
            'sku.required' => 'Product SKU is required',
            'sku.unique' => 'This SKU already exists',
            'barcode.unique' => 'This barcode already exists',
            'price.required' => 'Product price is required',
            'price.numeric' => 'Price must be a valid number',
            'stock_quantity.required' => 'Stock quantity is required',
            'stock_quantity.integer' => 'Stock quantity must be a whole number',
        ];
    }
}
