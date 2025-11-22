<?php

namespace App\Http\Resources\Inventories\Feature;

use App\Http\Resources\Inventories\FeatureValue\FeatureValueShortListResource;
use App\Models\Inventories\Features\Feature;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryFeatureRawResource", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "slug"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="name", type="string", example="Size"),
 *         @OA\Property(property="slug", type="string", example="size"),
 *         @OA\Property(property="values", type="array", description="Feature values",
 *             @OA\Items(ref="#/components/schemas/InventoryFeatureValueRawShort")
 *         ),
 *     )}
 * )
 *
 * @mixin Feature
 */

class FeatureForInventoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'values' => $this->isMultiple()
                ? FeatureValueShortListResource::collection($this->inventoryValues)
                : FeatureValueShortListResource::make($this->inventoryValues[0])
        ];
    }
}

