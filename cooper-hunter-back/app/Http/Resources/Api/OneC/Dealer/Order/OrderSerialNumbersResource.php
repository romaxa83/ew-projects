<?php

namespace App\Http\Resources\Api\OneC\Dealer\Order;

use App\Models\Orders\Dealer\SerialNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SerialNumber
 */
class OrderSerialNumbersResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'serial_number' => $this->serial_number,
            'product_guid' => $this->product->guid,
            'product_title' => $this->product->title,
        ];
    }
}
