<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Order;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeOrderStatusRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'status' => [
                'required',
                Rule::in(
                    [
                        Order::CALCULATED_STATUS_NEW,
                        Order::CALCULATED_STATUS_ASSIGNED,
                        Order::CALCULATED_STATUS_PICKED_UP,
                        Order::CALCULATED_STATUS_DELIVERED,
                    ]
                )
            ],
        ];

        /**@var Order $order*/
        $order = $this->order;

        if (
            $this->input('status') === Order::CALCULATED_STATUS_PICKED_UP
            && $order->isStatusAssigned()
        ) {
            $rules['pickup_date_actual'] = ['required', 'date_format:m/d/Y'];
        }

        if ($this->input('status') === Order::CALCULATED_STATUS_DELIVERED) {
            if ($order->isStatusAssigned()) {
                $rules['pickup_date_actual'] = ['required', 'date_format:m/d/Y'];
                $rules['delivery_date_actual'] = ['required', 'date_format:m/d/Y'];
            } elseif ($order->isStatusPickedUp()) {
                $rules['delivery_date_actual'] = ['required', 'date_format:m/d/Y'];
            }
        }

        return $rules;
    }
}
