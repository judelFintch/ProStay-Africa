<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['required', 'integer', 'exists:orders,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'stay_id' => ['nullable', 'exists:stays,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'due_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
