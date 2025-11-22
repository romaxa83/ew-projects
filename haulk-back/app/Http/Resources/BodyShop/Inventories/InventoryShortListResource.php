<?php

namespace App\Http\Resources\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Inventory
 */
class InventoryShortListResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="InventoryRawShort",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "name", "stock_number", "price_wholesale", "quantity"},
     *             @OA\Property(property="id", type="integer", description="Supplier id"),
     *             @OA\Property(property="name", type="string", description="Supplier Name"),
     *             @OA\Property(property="stock_number", type="string", description="Inventory Stock number"),
     *             @OA\Property(property="price", type="number", description="Inventory retail price"),
     *             @OA\Property(
     *                 property="unit",
     *                 description="Inventory Unit data",
     *                 type="object",
     *                 allOf={
     *                     @OA\Schema(
     *                         required={"id", "name", "accept_decimals"},
     *                         @OA\Property(property="name", type="string", description="Unit Name"),
     *                         @OA\Property(property="id", type="integer", description="Unit id"),
     *                         @OA\Property(property="accept_decimals", type="boolean", description="Unit accept decimals"),
     *                     )
     *                 }
     *             ),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="InventoryShortList",
     *     @OA\Property(
     *         property="data",
     *         description="Inventory paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/InventoryRawShort")
     *     ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock_number' => $this->stock_number,
            'price' => $this->price_retail,
            'unit' => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'accept_decimals' => $this->unit->accept_decimals,
            ],
        ];
    }
}
