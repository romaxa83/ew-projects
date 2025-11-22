<?php

namespace App\Http\Resources\Inventories\Feature;

use App\Http\Resources\Inventories\FeatureValue\FeatureValueECommResource;
use App\Models\Inventories\Features\Feature;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="FeatureECommRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"id", "name", "slug"},
 *          @OA\Property(property="id", type="integer", example="1"),
 *          @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *          @OA\Property(property="slug", type="string", example="ram-cooling-system-parts"),
 *          @OA\Property(property="position", type="integer", example="12"),
 *          @OA\Property(property="active", type="boolean"),
 *          @OA\Property(property="multiple", type="boolean"),
 *          @OA\Property(property="created_at", type="integer"),
 *          @OA\Property(property="updated_at", type="integer"),
 *          @OA\Property(property="values", type="array", description="Feature values",
 *              @OA\Items(ref="#/components/schemas/FeatureValueECommRaw")
 *          ),
 *      )}
 * )
 *
 * @OA\Schema(schema="FeatureECommResource",
 *     @OA\Property(property="data", description="Inventory feature list", type="array",
 *         @OA\Items(ref="#/components/schemas/FeatureECommRaw")
 *     ),
 * )
 *
 * @mixin Feature
 */

class FeatureECommResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'position' => $this->position,
            'active' => $this->active,
            'multiple' => $this->multiple,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
            'values' => FeatureValueECommResource::collection($this->values),
        ];
    }
}
