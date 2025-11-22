<?php

namespace App\Http\Resources\Inventories\Feature;

use App\Models\Inventories\Features\Feature;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryFeatureRawShort", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="name", type="string", example="Bosch"),
 *     )}
 * )
 *
 * @OA\Schema(schema="InventoryFeatureShortListResource",
 *     @OA\Property(property="data", description="Feature short list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryFeatureRawShort")
 *     ),
 * )
 *
 * @mixin Feature
 */
class FeatureShortListResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
