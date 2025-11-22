<?php

namespace App\Http\Resources\Inventories\Feature;

use App\Http\Resources\Inventories\FeatureValue\FeatureValueShortListResource;
use App\Models\Inventories\Features\Feature;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryFeatureResource", type="object",
 *     @OA\Property(property="data", type="object", description="Feature data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "slug"},
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="name", type="string", example="Size"),
 *             @OA\Property(property="slug", type="string", example="size"),
 *             @OA\Property(property="position", type="integer", example="2"),
 *             @OA\Property(property="multiple", type="boolean", example="true"),
 *             @OA\Property(property="active", type="boolean", example="true"),
 *             @OA\Property(property="values", type="array", description="Feature values",
 *                 @OA\Items(ref="#/components/schemas/InventoryFeatureValueRawShort")
 *             ),
 *         )}
 *     ),
 * )
 *
 * @mixin Feature
 */

class FeatureResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'position' => $this->position,
            'multiple' => $this->multiple,
            'active' => $this->active,
            'values' => FeatureValueShortListResource::collection($this->values)
        ];
    }
}
