<?php

namespace App\Http\Resources\Api\OneC\Orders;

use App\Enums\Orders\OrderArrivedFormEnum;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'arrived_from' => OrderArrivedFormEnum::TECH,
            'serial_number' => $this->serial_number,
            'model' => $this->product->title,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => (string)$this->phone,
            'comment' => $this->comment,
            'parts' => OrderPartResource::collection($this->parts),
            'shipping' => OrderShippingResource::make($this->shipping),
            'payment' => OrderPaymentResource::make($this->payment),
        ];
    }
}
