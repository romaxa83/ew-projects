<?php

namespace App\Http\Resources\BodyShop\TypesOfWork;

use App\Models\BodyShop\TypesOfWork\TypeOfWorkInventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TypeOfWorkInventory
 */
class TypeOfWorkInventoryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TypeOfWorkInventory",
     *     type="object",
     *     allOf={
     *          @OA\Schema(
     *             required={"id", "name", "stock_number", "quantiry", "price"},
     *             @OA\Property(property="id", type="integer", description="Type Of Work Entity id"),
     *             @OA\Property(property="inventory_id", type="integer", description="Type Of Work Inventory id"),
     *             @OA\Property(property="name", type="string", description="Type Of Work Inventory Name"),
     *             @OA\Property(property="stock_number", type="string", description="Type Of Work Inventory Stock Number"),
     *             @OA\Property(property="quantity", type="number", description="Type Of Work Quantity"),
     *             @OA\Property(property="price", type="number", description="Type Of Work price"),
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
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'inventory_id' => $this->inventory_id,
            'name' => $this->inventory->name,
            'stock_number' => $this->inventory->stock_number,
            'price' => $this->inventory->price_retail,
            'quantity' => $this->quantity,
            'unit' => [
                'id' => $this->inventory->unit->id,
                'name' => $this->inventory->unit->name,
                'accept_decimals' => $this->inventory->unit->accept_decimals,
            ],
        ];
    }
}
