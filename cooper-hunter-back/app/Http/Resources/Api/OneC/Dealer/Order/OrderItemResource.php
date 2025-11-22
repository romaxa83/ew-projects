<?php

namespace App\Http\Resources\Api\OneC\Dealer\Order;

use App\Models\Orders\Dealer\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Item
 */
class OrderItemResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'product_guid' => $this->product->guid,
            'product_title' => $this->product->title,
            'price' => $this->price,
            'qty' => $this->qty,
            'discount' => $this->discount,
            'total' => $this->total,
            'description' => $this->description,
        ];
    }
}
