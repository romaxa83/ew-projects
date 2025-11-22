<?php

namespace App\Http\Resources\Inventories\Inventory;

use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Inventories\Brand\BrandShortListResource;
use App\Http\Resources\Inventories\Unit\UnitShortResource;
use App\Models\Inventories\Inventory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryForOrderRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "stock_number", "price"},
 *         @OA\Property(property="id", type="integer", description="Inventory id"),
 *         @OA\Property(property="name", type="string", description="Inventory Name"),
 *         @OA\Property(property="stock_number", type="string", description="Inventory Stock number"),
 *         @OA\Property(property="article_number", type="string", description="Inventory article number"),
 *         @OA\Property(property="price", type="number", description="Inventory retail price"),
 *         @OA\Property(property="old_price", type="number", description="Inventory old price"),
 *         @OA\Property(property="discount", type="number", description="Inventory discount"),
 *         @OA\Property(property="delivery_cost", type="number", description="Inventory delivery_cost"),
 *         @OA\Property(property="quantity", type="number", description="Inventory quantity"),
 *         @OA\Property(property="main_image", type="object", description="Main inventory image", allOf={
 *             @OA\Schema(ref="#/components/schemas/Image")
 *         }),
 *         @OA\Property(property="brand", type="object", ref="#/components/schemas/InventoryBrandRawShort"),
 *         @OA\Property(property="unit", type="object", ref="#/components/schemas/InventoryUnitShortRaw"),
 *     )}
 * )
 *
 * @OA\Schema(schema="InventoryForPartOrderResource",
 *     @OA\Property(property="data", description="Inventory list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryForOrderRaw")
 *     ),
 * )
 *
 * @mixin Inventory
 */
class InventoryForPartOrderResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock_number' => $this->stock_number,
            'article_number' => $this->article_number,
            'price' => $this->price_retail,
            'old_price' => $this->old_price,
            'discount' => $this->discount,
            'delivery_cost' => $this->delivery_cost,
            'quantity' => $this->quantity,
            Inventory::MAIN_IMAGE_FIELD_NAME => ImageResource::make($this->getMainImg()),
            'brand' => BrandShortListResource::make($this->brand),
            'unit' => UnitShortResource::make($this->unit),
        ];
    }
}

