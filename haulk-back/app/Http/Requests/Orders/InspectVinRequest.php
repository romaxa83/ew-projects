<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Order;
use Illuminate\Foundation\Http\FormRequest;

class InspectVinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'vin' => ['required_without:' . Order::VIN_SCAN_FIELD_NAME, 'string', 'max:100'],
            Order::VIN_SCAN_FIELD_NAME => ['required_without:vin', 'file'],
        ];
    }
}
