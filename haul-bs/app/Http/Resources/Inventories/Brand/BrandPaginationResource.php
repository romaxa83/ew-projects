<?php

namespace App\Http\Resources\Inventories\Brand;

use App\Models\Inventories\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryBrandRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Brand data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "slug"},
 *             @OA\Property(property="id", type="integer", description="Brand id"),
 *             @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *             @OA\Property(property="slug", type="string", example="ram-cooling-system-parts"),
 *             @OA\Property(property="hasRelatedEntities", type="boolean", example="true", description="Is Inventory brand can be deleted"),
 *         )
 *      }),
 *  )
 *
 * @OA\Schema(schema="InventoryBrandPaginationResource",
 *      @OA\Property(property="data", description="Brand paginated list", type="array",
 *          @OA\Items(ref="#/components/schemas/InventoryBrandRaw")
 *      ),
 *      @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *      @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 *  )
 *
 * @mixin Brand
 */
class BrandPaginationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'hasRelatedEntities' => $this->hasRelatedEntities(),
        ];
    }
}
