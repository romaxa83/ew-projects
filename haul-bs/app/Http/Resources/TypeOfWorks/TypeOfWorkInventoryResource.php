<?php

namespace App\Http\Resources\TypeOfWorks;

use App\Http\Resources\Inventories\Unit\UnitShortResource;
use App\Models\TypeOfWorks\TypeOfWorkInventory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="TypeOfWorkInventory", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "stock_number", "quantiry", "price"},
 *         @OA\Property(property="id", type="integer", description="Type Of Work Entity id"),
 *         @OA\Property(property="inventory_id", type="integer", description="Type Of Work Inventory id"),
 *         @OA\Property(property="name", type="string", description="Type Of Work Inventory Name"),
 *         @OA\Property(property="stock_number", type="string", description="Type Of Work Inventory Stock Number"),
 *         @OA\Property(property="article_number", type="string", description="Type Of Work Inventory article_number"),
 *         @OA\Property(property="quantity", type="number", description="Type Of Work Quantity"),
 *         @OA\Property(property="price", type="number", description="Type Of Work price"),
 *         @OA\Property(property="unit", type="object", ref="#/components/schemas/InventoryUnitShortRaw"),
 *     )}
 * )
 *
 * @mixin TypeOfWorkInventory
 */
class TypeOfWorkInventoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'inventory_id' => $this->inventory_id,
            'name' => $this->inventory->name,
            'stock_number' => $this->inventory->stock_number,
            'article_number' => $this->inventory->article_number,
            'price' => $this->inventory->price_retail,
            'quantity' => $this->quantity,
            'unit' => UnitShortResource::make($this->inventory->unit),
        ];
    }
}
