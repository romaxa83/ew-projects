<?php

namespace App\Http\Resources\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Inventory
 */
class InventoryPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="InventoryRaw",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Inventory data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "name", "stock_number", "price_wholesale", "quantity"},
     *                     @OA\Property(property="id", type="integer", description="Supplier id"),
     *                     @OA\Property(property="name", type="string", description="Supplier Name"),
     *                     @OA\Property(property="stock_number", type="string", description="Inventory Stock number"),
     *                     @OA\Property(property="price_retail", type="number", description="Inventory retail price"),
     *                     @OA\Property(property="quantity", type="integer", description="Inventory quantity"),
     *                     @OA\Property(property="status", type="string", description="Inventory status"),
     *                     @OA\Property(property="category_name", type="string", description="Inventory Category Name"),
     *                     @OA\Property(property="supplier_name", type="string", description="Inventory Supplier Name"),
     *                     @OA\Property(property="unit_name", type="string", description="Inventory Unit Name"),
     *                     @OA\Property(property="accept_decimals", type="bool", description="Inventory is accept decimals"),
     *                     @OA\Property(property="running_out_of_stock", type="bool", description="Running out of stock"),
     *                     @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Is inventory has related open orders"),
     *                     @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Is inventory has related deleted orders"),
     *                     @OA\Property(property="hasRelatedTypesOfWork", type="boolean", description="Is inventory has related types of work"),
     *                     @OA\Property(property="length", type="number"),
     *                     @OA\Property(property="width", type="number"),
     *                     @OA\Property(property="height", type="number"),
     *                     @OA\Property(property="weight", type="number"),
     *                     @OA\Property(property="min_limit_price", type="number"),
     *                 )
     *             }
     *         ),
     * )
     *
     * @OA\Schema(
     *     schema="InventoryPaginate",
     *     @OA\Property(
     *         property="data",
     *         description="Inventory paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/InventoryRaw")
     *     ),
     *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock_number' => $this->stock_number,
            'price_retail' => $this->price_retail,
            'quantity' => $this->quantity,
            'min_limit' => $this->min_limit,
            'for_sale' => $this->for_sale,
            'running_out_of_stock' => $this->min_limit && $this->quantity <= $this->min_limit,
            'status' => $this->getStatus(),
            'category_name' => $this->category->name ?? null,
            'supplier_name' => $this->supplier->name ?? null,
            'unit_name' => $this->unit->name,
            'accept_decimals' => $this->unit->accept_decimals,
            'hasRelatedOpenOrders' => $this->hasRelatedOpenOrders(),
            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedOrders(),
            'hasRelatedTypesOfWork' => $this->hasRelatedTypesOfWork(),
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'weight' => $this->weight,
            'min_limit_price' => $this->min_limit_price,
        ];
    }
}
