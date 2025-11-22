<?php

namespace App\Http\Requests\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property Order $order
 */
class OrderChangeStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in($this->statusIn()),
                function ($attribute, $value, $fail) {
                    if (!$this->order->isStatusCanBeChanged()) {
                        $fail(trans('Order status can\'t be changed.'));
                    }
                }
            ],
        ];
    }

    private function statusIn(): array
    {
        $statusesArr = [
            Order::STATUS_NEW => [
                Order::STATUS_IN_PROCESS,
                Order::STATUS_FINISHED,
            ],
            Order::STATUS_IN_PROCESS => [
                Order::STATUS_NEW,
                Order::STATUS_FINISHED,
            ],
            Order::STATUS_FINISHED => [
                Order::STATUS_NEW,
                Order::STATUS_IN_PROCESS,
            ],
        ];

        return $statusesArr[$this->order->status] ?? [];
    }
}
