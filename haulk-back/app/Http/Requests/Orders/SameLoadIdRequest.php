<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SameLoadIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => [
                'nullable',
                'int',
                Rule::exists(Order::class, 'id'),
            ],
            'load_id' => [
                'required',
                'string',
                'max:255'
            ],
        ];
    }

    public function getLoadId(): string
    {
        return $this->validated()['load_id'];
    }

    public function getOrderId(): ?int
    {
        return $this->validated()['order_id'] ?? null;
    }
}
