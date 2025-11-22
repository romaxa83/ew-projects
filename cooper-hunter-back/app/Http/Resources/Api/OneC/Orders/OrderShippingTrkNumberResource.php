<?php

namespace App\Http\Resources\Api\OneC\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderShippingTrkNumberResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'number' => $number = $this->resource['number'],
            'tracking_url' => $number ? config('orders.tracking_url') . $number : null
        ];
    }
}
