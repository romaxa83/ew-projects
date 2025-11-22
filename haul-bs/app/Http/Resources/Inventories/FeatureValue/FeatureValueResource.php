<?php

namespace App\Http\Resources\Inventories\FeatureValue;

use App\Models\Inventories\Features\Value;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryFeatureValueResource", type="object",
 *     @OA\Property(property="data", type="object", description="Feature value data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "slug"},
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="name", type="string", example="Size"),
 *             @OA\Property(property="slug", type="string", example="size"),
 *             @OA\Property(property="position", type="integer", example="2"),
 *             @OA\Property(property="feature_id", type="integer", example="2"),
 *         )}
 *     ),
 * )
 *
 * @mixin Value
 */

class FeatureValueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'position' => $this->position,
            'feature_id' => $this->feature_id,
        ];
    }
}
