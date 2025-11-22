<?php

namespace App\Http\Resources\Inventories\Category;

use App\Foundations\Modules\Seo\Deliveries\Http\Resources\SeoResource;
use App\Http\Resources\Files\ImageResource;
use App\Models\Inventories\Category;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryCategoryECommRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"id", "name", "slug"},
 *          @OA\Property(property="id", type="integer", example="1"),
 *          @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *          @OA\Property(property="slug", type="string", example="ram-cooling-system-parts"),
 *          @OA\Property(property="desc", type="string", example="ram-cooling-system-parts"),
 *          @OA\Property(property="position", type="integer", example="12"),
 *          @OA\Property(property="parent_id", type="integer"),
 *          @OA\Property(property="active", type="boolean"),
 *          @OA\Property(property="display_menu", type="boolean"),
 *          @OA\Property(property="created_at", type="integer"),
 *          @OA\Property(property="updated_at", type="integer"),
 *          @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoResource"),
 *          @OA\Property(property="header_image", type="object", allOf={
 *              @OA\Schema(ref="#/components/schemas/Image")
 *          }),
 *          @OA\Property(property="menu_image", type="object", allOf={
 *              @OA\Schema(ref="#/components/schemas/Image")
 *          }),
 *          @OA\Property(property="mobile_image", type="object", description="image for mobile with different size", allOf={
 *              @OA\Schema(ref="#/components/schemas/Image")
 *          }),
 *      )}
 * )
 *
 * @OA\Schema(schema="CategoryECommResource",
 *     @OA\Property(property="data", description="Inventory category list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryCategoryECommRaw")
 *     ),
 * )
 *
 * @mixin Category
 */

class CategoryECommResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'desc' => $this->desc,
            'position' => $this->position,
            'parent_id' => $this->parent_id,
            'active' => $this->active,
            'display_menu' => $this->display_menu,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
            'seo' => SeoResource::make($this->seo),
            Category::IMAGE_HEADER_FIELD_NAME => ImageResource::make($this->getHeaderImg()),
            Category::IMAGE_MENU_FIELD_NAME => ImageResource::make($this->getMenuImg()),
            Category::IMAGE_MOBILE_FIELD_NAME => ImageResource::make($this->getMobileImg()),
        ];
    }
}
