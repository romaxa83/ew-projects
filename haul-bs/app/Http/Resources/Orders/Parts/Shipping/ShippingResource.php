<?php

namespace App\Http\Resources\Orders\Parts\Shipping;

use App\Http\Resources\Orders\Parts\ItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 *
 * @OA\Schema(schema="ShippingResource", type="object",
 *     @OA\Property(property="data", type="array", description="Shipping methods and items for parts order data",
 *         @OA\Items(allOf={
 *             @OA\Schema(
 *                 @OA\Property(property="methods", description="Shipping methods", type="array",
 *                     @OA\Items(ref="#/components/schemas/ShippingMethodRaw")
 *                 ),
 *                 @OA\Property(property="items", description=" Items for this Shipping methods", type="array",
 *                     @OA\Items(ref="#/components/schemas/OrderPartsItemRaw")
 *                 ),
 *             )
 *         })
 *     ),
 * )
 *
 * @mixin array
 */
class ShippingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'methods' => ShippingMethodResource::collection($this['methods'] ?? null),
            'items' => ItemResource::collection($this['items'] ?? null),
        ];
    }
}


