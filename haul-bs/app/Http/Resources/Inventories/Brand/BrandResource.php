<?php

namespace App\Http\Resources\Inventories\Brand;

use App\Foundations\Modules\Seo\Deliveries\Http\Resources\SeoResource;
use App\Models\Inventories\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryBrandResource", type="object",
 *     @OA\Property(property="data", type="object", description="Brand data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "slug"},
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="name", type="string", example="Bosch"),
 *             @OA\Property(property="slug", type="string", example="bosch"),
 *             @OA\Property(property="hasRelatedEntities", type="boolean", example="true", description="Is Inventory brand can be deleted"),
 *             @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoResource"),
 *         )}
 *     ),
 * )
 *
 * @mixin Brand
 */

class BrandResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'hasRelatedEntities' => $this->hasRelatedEntities(),
            'seo' => SeoResource::make($this->seo),
        ];
    }
}
