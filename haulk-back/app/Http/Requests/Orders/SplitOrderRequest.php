<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SplitOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_load_id' => [
                'required',
                'string',
                'min:2',
                'max:255'
            ],
            'destination' => [
                'required',
                'array'
            ],
            'destination.*.load_id' => [
                'required',
                'string',
                'min:2',
                'max:255'
            ],
            'destination.*.vehicles' => [
                'required',
                'array'
            ],
            'destination.*.vehicles.*' => [
                'required',
                'int',
                Rule::exists(Vehicle::class, 'id')
            ],
        ];
    }
}
