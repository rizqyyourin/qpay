<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
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
            'order_id' => 'required|integer|exists:orders,id',
            'amount' => 'required|numeric|min:0|decimal:0,2',
            'payment_method' => 'required|string|in:cash,card,transfer,ewallet',
            'transaction_id' => 'nullable|string|unique:payments,transaction_id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_id.required' => 'Order is required',
            'order_id.exists' => 'Selected order does not exist',
            'amount.required' => 'Payment amount is required',
            'amount.numeric' => 'Amount must be a valid number',
            'amount.min' => 'Amount must be greater than 0',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method selected',
            'transaction_id.unique' => 'This transaction ID already exists',
        ];
    }
}
