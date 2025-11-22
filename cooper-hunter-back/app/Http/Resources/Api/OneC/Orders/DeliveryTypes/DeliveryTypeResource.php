<?php

namespace App\Http\Resources\Api\OneC\Orders\DeliveryTypes;

use App\Models\Orders\Deliveries\OrderDeliveryType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderDeliveryType
 */
class DeliveryTypeResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at->getTimestamp(),
            'updated_at' => $this->updated_at->getTimestamp(),
            'active' => $this->active,
            'translations' => $this->translations,
        ];
    }
}
