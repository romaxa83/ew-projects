<?php

namespace App\Http\Resources\Api\OneC\Orders;

use App\Models\Orders\OrderPart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderPart
 */
class OrderPartResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'order_category_guid' => $this->orderCategory->guid,
            'order_category_id' => $this->order_category_id,
            'name' => $this->translation()->title,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'description' => $this->description,
        ];
    }
}
