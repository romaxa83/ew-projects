<?php

namespace App\Http\Resources\Inventories\Brand;

use App\Foundations\Modules\Seo\Deliveries\Http\Resources\SeoResource;
use App\Models\Inventories\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryBrandECommRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"id", "name", "slug"},
 *          @OA\Property(property="id", type="integer", example="1"),
 *          @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *          @OA\Property(property="slug", type="string", example="ram-cooling-system-parts"),
 *          @OA\Property(property="created_at", type="integer"),
 *          @OA\Property(property="updated_at", type="integer"),
 *          @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoResource"),
 *      )}
 * )
 *
 * @OA\Schema(schema="BrandECommResource",
 *     @OA\Property(property="data", description="Inventory category list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryBrandECommRaw")
 *     ),
 * )
 *
 * @mixin Brand
 */

class BrandECommResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
            'seo' => SeoResource::make($this->seo),
        ];
    }
}
