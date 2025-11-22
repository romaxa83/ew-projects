<?php

namespace App\Http\Resources\Inventories\Inventory;

use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Inventories\Brand\BrandShortListResource;
use App\Models\Inventories\Inventory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryRawForListPaginate", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "stock_number", "price"},
 *         @OA\Property(property="id", type="integer", description="Inventory id"),
 *         @OA\Property(property="name", type="string", description="Inventory Name"),
 *         @OA\Property(property="stock_number", type="string", description="Inventory Stock number"),
 *         @OA\Property(property="article_number", type="string", description="Inventory article number"),
 *         @OA\Property(property="discount", type="number", description="Inventory discount"),
 *         @OA\Property(property="delivery_cost", type="number", description="Inventory delivery_cost"),
 *         @OA\Property(property="price", type="number", description="Inventory retail price"),
 *         @OA\Property(property="price_old", type="number", description="Inventory old price"),
 *         @OA\Property(property="quantity", type="number", description="Inventory quantity"),
 *         @OA\Property(property="brand", type="object", ref="#/components/schemas/InventoryBrandRawShort"),
 *         @OA\Property(property="unit", description="Inventory Unit data", type="object", allOf={
 *             @OA\Schema(
 *                 required={"id", "name", "accept_decimals"},
 *                 @OA\Property(property="name", type="string", description="Unit Name"),
 *                 @OA\Property(property="id", type="integer", description="Unit id"),
 *                 @OA\Property(property="accept_decimals", type="boolean", description="Unit accept decimals"),
 *             )}
 *         ),
 *     )}
 * )
 *
 * @OA\Schema(schema="InventoryShortListPaginateResource",
 *      @OA\Property(property="data", description="Inventory paginated list", type="array",
 *          @OA\Items(ref="#/components/schemas/InventoryRawForListPaginate")
 *      ),
 *      @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *      @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 * )
 *
 * @mixin Inventory
 */
class InventoryShortListPaginateResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock_number' => $this->stock_number,
            'article_number' => $this->article_number,
            'discount' => $this->discount,
            'delivery_cost' => $this->delivery_cost,
            'price' => $this->price_retail,
            'price_old' => $this->old_price,
            'quantity' => $this->quantity,
            Inventory::MAIN_IMAGE_FIELD_NAME => ImageResource::make($this->getMainImg()),
            'unit' => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'accept_decimals' => $this->unit->accept_decimals,
            ],
            'brand' => BrandShortListResource::make($this->brand),
        ];
    }
}
