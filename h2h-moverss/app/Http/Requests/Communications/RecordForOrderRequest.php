<?php

namespace App\Http\Requests\Communications;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordForOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orderID' => ['required', 'integer', Rule::exists(Order::TABLE, 'id')],
            'historyTill' => ['nullable', 'date_format:U'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
