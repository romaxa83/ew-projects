<?php

namespace App\Http\Resources\Inventories\Feature;

use App\Models\Inventories\Features\Feature;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryFeatureRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Feature data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "slug"},
 *             @OA\Property(property="id", type="integer", description="Feature id"),
 *             @OA\Property(property="name", type="string", example="size"),
 *             @OA\Property(property="slug", type="string", example="size"),
 *             @OA\Property(property="active", type="boolean", example="true"),
 *             @OA\Property(property="position", type="integer", example="1"),
 *             @OA\Property(property="multiple", type="boolean", example="true"),
 *         )
 *      }),
 *  )
 *
 * @OA\Schema(schema="InventoryFeaturePaginationResource",
 *      @OA\Property(property="data", description="Feature paginated list", type="array",
 *          @OA\Items(ref="#/components/schemas/InventoryFeatureRaw")
 *      ),
 *      @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *      @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 *  )
 *
 * @mixin Feature
 */
class FeaturePaginationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'active' => $this->active,
            'position' => $this->position,
            'multiple' => $this->multiple,
        ];
    }
}
