<?php

namespace App\Http\Resources\Orders\Parts;

use App\Models\Orders\Parts\Shipping;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="ShippingRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"id", "name", "cost"},
 *          @OA\Property(property="id", type="integer", description="Item id"),
 *          @OA\Property(property="name", type="string", description="Shipping method name"),
 *          @OA\Property(property="cost", type="number", description="Shipping method cost"),
 *          @OA\Property(property="terms", type="string", description="Shipping method terms"),
 *      )
 * })
 *
 * @mixin Shipping
 */
class ShippingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->method,
            'cost' => $this->cost,
            'terms' => $this->terms,
//            'items' => ItemResource::collection($this->items)
        ];
    }
}
