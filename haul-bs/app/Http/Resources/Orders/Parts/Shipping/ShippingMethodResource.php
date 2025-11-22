<?php

namespace App\Http\Resources\Orders\Parts\Shipping;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 *
 * @OA\Schema(schema="ShippingMethodRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"name", "cost"},
 *         @OA\Property(property="name", type="string", example="FedEx Ground"),
 *         @OA\Property(property="cost", type="number", example="22.33"),
 *         @OA\Property(property="terms", type="string", example="1 business day"),
 *     )
 * })
 *
 * @OA\Schema(schema="ShippingMethodResource",
 *      @OA\Property(property="data", description="Shipping methods for parts order data", type="array",
 *          @OA\Items(ref="#/components/schemas/ShippingMethodRaw")
 *      ),
 *  )
 *
 * @return array
 */
class ShippingMethodResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this['name'],
            'cost' => $this['cost'],
            'terms' => $this['terms'],
        ];
    }
}

