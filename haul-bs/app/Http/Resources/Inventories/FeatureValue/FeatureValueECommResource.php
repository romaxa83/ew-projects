<?php

namespace App\Http\Resources\Inventories\FeatureValue;

use App\Models\Inventories\Features\Value;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="FeatureValueECommRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Feature data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "slug"},
 *             @OA\Property(property="id", type="integer", description="id"),
 *             @OA\Property(property="name", type="string", example="size"),
 *             @OA\Property(property="slug", type="string", example="size"),
 *             @OA\Property(property="position", type="integer", example="1"),
 *             @OA\Property(property="active", type="boolean", example="true"),
 *             @OA\Property(property="created_at", type="integer"),
 *             @OA\Property(property="updated_at", type="integer"),
 *         )
 *      }),
 *  )
 *
 * @mixin Value
 */
class FeatureValueECommResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'position' => $this->position,
            'active' => $this->active,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }
}
