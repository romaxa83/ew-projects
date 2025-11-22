<?php

namespace App\Http\Resources\Inventories\Category;

use App\Foundations\Modules\Seo\Deliveries\Http\Resources\SeoResource;
use App\Http\Resources\Files\ImageResource;
use App\Models\Inventories\Category;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryCategoryRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "slug"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *         @OA\Property(property="slug", type="string", example="ram-cooling-system-parts"),
 *         @OA\Property(property="desc", type="string", example="some description"),
 *         @OA\Property(property="parent_id", type="integer", example="12"),
 *         @OA\Property(property="position", type="integer", example="1"),
 *         @OA\Property(property="hasRelatedEntities", type="boolean", example="true", description="Is Inventory category can be deleted"),
 *         @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoResource"),
 *         @OA\Property(property="header_image", type="object", description="image for header with different size", allOf={
 *             @OA\Schema(ref="#/components/schemas/Image")
 *         }),
 *         @OA\Property(property="menu_image", type="object", description="image for menu with different size", allOf={
 *             @OA\Schema(ref="#/components/schemas/Image")
 *         }),
 *     )}
 * )
 *
 * @OA\Schema(schema="UnitCategoryListResource",
 *     @OA\Property(property="data", description="Inventory category list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryCategoryRaw")
 *     ),
 * )
 *
 * @OA\Schema(schema="UnitCategoryResource", type="object",
 *     @OA\Property(property="data", type="object", description="Tag data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "slug"},
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *             @OA\Property(property="slug", type="string", example="ram-cooling-system-parts"),
 *             @OA\Property(property="desc", type="string", example="some description"),
 *             @OA\Property(property="parent_id", type="integer", example="12"),
 *             @OA\Property(property="position", type="integer", example="1"),
 *             @OA\Property(property="display_menu", type="boolean", example="true"),
 *             @OA\Property(property="hasRelatedEntities", type="boolean", example="true", description="Is Inventory category can be deleted"),
 *             @OA\Property(property="hasChildrenRelatedEntities", type="boolean", example="true", description="Do child entities have a relationship?"),
 *             @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoResource"),
 *             @OA\Property(property="header_image", type="object", description="image for header with different size", allOf={
 *                 @OA\Schema(ref="#/components/schemas/Image")
 *             }),
 *             @OA\Property(property="menu_image", type="object", description="image for menu with different size", allOf={
 *                 @OA\Schema(ref="#/components/schemas/Image")
 *             }),
 *             @OA\Property(property="mobile_image", type="object", description="image for mobile with different size", allOf={
 *                 @OA\Schema(ref="#/components/schemas/Image")
 *             }),
 *         )}
 *     ),
 * )
 *
 * @mixin Category
 */

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'desc' => $this->desc,
            'parent_id' => $this->parent_id,
            'position' => $this->position,
            'display_menu' => $this->display_menu,
            'hasRelatedEntities' => $this->hasRelatedEntities(),
            'hasChildrenRelatedEntities' => $this->hasChildrenRelatedEntities(),
            'seo' => SeoResource::make($this->seo),
            Category::IMAGE_HEADER_FIELD_NAME => ImageResource::make($this->getHeaderImg()),
            Category::IMAGE_MENU_FIELD_NAME => ImageResource::make($this->getMenuImg()),
            Category::IMAGE_MOBILE_FIELD_NAME => ImageResource::make($this->getMobileImg()),
        ];
    }
}
