<?php

namespace App\Http\Resources\Inventories\Brand;

use App\Models\Inventories\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryBrandRawShort", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="name", type="string", example="Bosch"),
 *     )}
 * )
 *
 * @OA\Schema(schema="InventoryBrandShortListResource",
 *     @OA\Property(property="data", description="Brand short list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryBrandRawShort")
 *     ),
 * )
 *
 * @mixin Brand
 */
class BrandShortListResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
