<?php

namespace App\Http\Resources\Api\OneC\Orders;

use App\Http\Resources\Api\OneC\Location\CountryResource;
use App\Http\Resources\Api\OneC\Location\StateResource;
use App\Http\Resources\Api\OneC\Orders\DeliveryTypes\DeliveryTypeResource;
use App\Models\Orders\OrderShipping;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderShipping
 */
class OrderShippingResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        // todo после заливки на прод , country и state оптимизировать
        return [
            'order_id' => $this->order_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => (string)$this->phone,
            'address_first_line' => $this->address_first_line,
            'address_second_line' => $this->address_second_line,
            'city' => $this->city,
//            'country' => $this->country,
//            'state' => $this->state,
            'country' => CountryResource::make($this->country()->first()),
            'state' => StateResource::make($this->state()->first()),
            'zip' => $this->zip,
            'trk_number' => OrderShippingTrkNumberResource::make(['number' => $this->trk_number]),
            'delivery_type' => DeliveryTypeResource::make($this->deliveryType),
        ];
    }
}
