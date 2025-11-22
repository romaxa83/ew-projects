<?php

namespace App\Http\Resources\Api\OneC\Dealer\Order;

use App\Models\Orders\Dealer\PackingSlipItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PackingSlipItem
 */
class PackingSlipItemResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'product_guid' => $this->product->guid,
            'product_title' => $this->product->title,
            'qty' => $this->qty,
            'description' => $this->description,
        ];
    }
}
