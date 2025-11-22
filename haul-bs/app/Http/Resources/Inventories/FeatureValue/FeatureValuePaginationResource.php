<?php

namespace App\Http\Resources\Inventories\FeatureValue;

use App\Models\Inventories\Features\Value;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryFeatureValueRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Feature data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "slug"},
 *             @OA\Property(property="id", type="integer", description="Feature id"),
 *             @OA\Property(property="name", type="string", example="size"),
 *             @OA\Property(property="slug", type="string", example="size"),
 *             @OA\Property(property="position", type="integer", example="1"),
 *             @OA\Property(property="feature_id", type="integer", example="2"),
 *         )
 *      }),
 *  )
 *
 * @OA\Schema(schema="InventoryFeatureValuePaginationResource",
 *      @OA\Property(property="data", description="Feature value paginated list", type="array",
 *          @OA\Items(ref="#/components/schemas/InventoryFeatureValueRaw")
 *      ),
 *      @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *      @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 *  )
 *
 * @mixin Value
 */
class FeatureValuePaginationResource extends JsonResource
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
