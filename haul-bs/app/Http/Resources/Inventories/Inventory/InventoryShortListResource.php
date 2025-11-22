<?php

namespace App\Http\Resources\Inventories\Inventory;

use App\Models\Inventories\Inventory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryRawShort", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "stock_number", "price"},
 *         @OA\Property(property="id", type="integer", description="Inventory id"),
 *         @OA\Property(property="name", type="string", description="Inventory Name"),
 *         @OA\Property(property="stock_number", type="string", description="Inventory Stock number"),
 *         @OA\Property(property="article_number", type="string", description="Inventory article number"),
 *         @OA\Property(property="price", type="number", description="Inventory retail price"),
 *         @OA\Property(property="quantity", type="number", description="Inventory quantity"),
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
 * @OA\Schema(schema="InventoryShortListResource",
 *     @OA\Property(property="data", description="Inventory list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryRawShort")
 *     ),
 * )
 *
 * @mixin Inventory
 */
class InventoryShortListResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock_number' => $this->stock_number,
            'article_number' => $this->article_number,
            'price' => (string)$this->price_retail,
            'quantity' => $this->quantity,
            'unit' => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'accept_decimals' => $this->unit->accept_decimals,
            ],
        ];
    }
}
