<?php

namespace App\Http\Resources\Api\OneC\Dealer\Order;

use App\Models\Orders\Dealer\Dimensions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Dimensions
 */
class DimensionsResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'pallet' => $this->pallet,
            'box_qty' => $this->box_qty,
            'type' => $this->type,
            'weight' => $this->weight,
            'width' => $this->width,
            'depth' => $this->depth,
            'height' => $this->height,
            'class_freight' => $this->class_freight,
        ];
    }
}
