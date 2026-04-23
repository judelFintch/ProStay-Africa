<?php

namespace App\Http\Requests;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_area_id' => ['nullable', 'exists:service_areas,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'stay_id' => ['nullable', 'exists:stays,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'dining_table_id' => ['nullable', 'exists:dining_tables,id'],
            'customer_type' => ['required', Rule::in(array_column(CustomerType::cases(), 'value'))],
            'status' => ['nullable', Rule::in(array_column(OrderStatus::cases(), 'value'))],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.menu_id' => ['nullable', 'exists:menus,id'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
