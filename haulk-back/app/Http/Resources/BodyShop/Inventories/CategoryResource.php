<?php

namespace App\Http\Resources\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="InventoryCategory",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Inventory category data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "name"},
     *                     @OA\Property(property="id", type="integer", description="Inventory category id"),
     *                     @OA\Property(property="name", type="string", description="Inventory category Name"),
     *                     @OA\Property(property="hasRelatedEntities", type="boolean", description="Is Inventory category can be deleted"),
     *                 )
     *             }
     *         ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'hasRelatedEntities' => $this->hasRelatedEntities(),
        ];
    }
}
