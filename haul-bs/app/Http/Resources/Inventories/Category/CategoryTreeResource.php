<?php

namespace App\Http\Resources\Inventories\Category;

use App\Models\Inventories\Category;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryCategoryTreeRawSingel", type="object", allOf={
 *      @OA\Schema(
 *          required={"id", "name", "slug"},
 *          @OA\Property(property="id", type="integer", example="1"),
 *          @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *          @OA\Property(property="slug", type="string", example="ram-cooling-system-parts"),
 *          @OA\Property(property="position", type="integer", example="12"),
 *          @OA\Property(property="hasRelatedEntities", type="boolean", example="true", description="Is Inventory category can be deleted"),
 *          @OA\Property(property="hasChildrenRelatedEntities", type="boolean", example="true", description="Do child entities have a relationship?"),
 *          @OA\Property(property="children", type="array", description="children categories",
 *              @OA\Items(type="string")
 *          ),
 *      )}
 * )
 *
 * @OA\Schema(schema="InventoryCategoryTreeRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "slug"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *         @OA\Property(property="slug", type="string", example="ram-cooling-system-parts"),
 *         @OA\Property(property="position", type="integer", example="12"),
 *         @OA\Property(property="hasRelatedEntities", type="boolean", example="true", description="Is Inventory category can be deleted"),
 *         @OA\Property(property="hasChildrenRelatedEntities", type="boolean", example="true", description="Do child entities have a relationship?"),
 *         @OA\Property(property="children", type="array", description="children categories",
 *             @OA\Items(ref="#/components/schemas/InventoryCategoryTreeRawSingel")
 *         ),
 *     )}
 * )
 *
 * @OA\Schema(schema="UnitCategoryListTreeResource",
 *     @OA\Property(property="data", description="Inventory category list as tree", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryCategoryTreeRaw")
 *     ),
 * )
 *
 * @mixin Category
 */

class CategoryTreeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'position' => $this->position,
            'hasRelatedEntities' => $this->hasRelatedEntities(),
            'hasChildrenRelatedEntities' => $this->hasChildrenRelatedEntities(),
            'children' => !empty($this->children)
                ? self::collection($this->children)
                : null,
        ];
    }
}
