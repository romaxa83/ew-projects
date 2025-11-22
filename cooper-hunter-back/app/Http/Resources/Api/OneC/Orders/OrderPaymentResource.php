<?php

namespace App\Http\Resources\Api\OneC\Orders;

use App\Models\Orders\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderPayment
 */
class OrderPaymentResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'cost_status' => $this->cost_status,
            'order_id' => $this->order_id,
            'order_price' => $this->order_price,
            'order_price_with_discount' => $this->order_price_with_discount,
            'shipping_cost' => $this->shipping_cost,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'paid_at' => $this->paid_at,
        ];
    }
}
