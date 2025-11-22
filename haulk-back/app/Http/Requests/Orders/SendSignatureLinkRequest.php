<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Order;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class SendSignatureLinkRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return $this->user()->can('orders send-signature-link');
    }

    public function rules(): array
    {
        return [
            'inspection_location' => [
                'required',
                'string',
                'in:' . Order::LOCATION_PICKUP . ',' . Order::LOCATION_DELIVERY
            ],
            'email' => [
                'required',
                'email'
            ]
        ];
    }

}
