<?php

namespace App\Http\Resources\Api\OneC\Products;

use App\Models\Catalog\Products\ProductSerialNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductSerialNumber
 */
class SerialNumberResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'product_id' => $this->product_id,
            'product_title' => $this->product->title,
            'serial_numbers' => $this->serial_number,
        ];
    }
}
