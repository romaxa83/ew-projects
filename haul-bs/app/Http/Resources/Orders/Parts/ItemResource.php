<?php

namespace App\Http\Resources\Orders\Parts;

use App\Http\Resources\Inventories\Inventory\InventoryForPartOrderResource;
use App\Models\Orders\Parts\Item;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="OrderPartsItemRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"id", "quantity"},
 *          @OA\Property(property="id", type="integer", description="Item id"),
 *          @OA\Property(property="quantity", type="string", description="Inventory quantity"),
 *          @OA\Property(property="free_shipping", type="boolean", description="Inventory is a free shipping"),
 *          @OA\Property(property="base_price", type="number", description="Inventory price"),
 *          @OA\Property(property="price", type="number", description="Inventory price (price+delivery_cost - discount %)"),
 *          @OA\Property(property="price_old", type="number", description="Inventory old price"),
 *          @OA\Property(property="delivery_cost", type="number", description="Inventory delivery cost"),
 *          @OA\Property(property="discount", type="number", description="Discount by item"),
 *          @OA\Property(property="is_overload", type="boolean", description="Inventory delivery cost"),
 *          @OA\Property(property="inventory", type="object", ref="#/components/schemas/InventoryForOrderRaw"),
 *      )
 * })
 *
 * @OA\Schema(schema="OrderPartsItemResource", type="object",
 *     @OA\Property(property="data", type="object", allOf={
 *         @OA\Schema(ref="#/components/schemas/OrderPartsItemRaw")
 *     })
 * )
 *
 * @OA\Schema(schema="OrderPartsItemListResource",
 *     @OA\Property(property="data", description="Item list", type="array",
 *         @OA\Items(ref="#/components/schemas/OrderPartsItemRaw")
 *     ),
 *  )
 *
 * @mixin Item
 */
class ItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => $this->getQty(),
            'free_shipping' => $this->free_shipping,
            'base_price' => $this->price,
            'price' => $this->getPrice(),
            'price_old' => $this->getPriceOld(),
            'delivery_cost' => $this->delivery_cost,
            'discount' => $this->discount,
            'is_overload' => $this->isOverload(),
            'inventory' => InventoryForPartOrderResource::make($this->inventory),
        ];
    }
}
