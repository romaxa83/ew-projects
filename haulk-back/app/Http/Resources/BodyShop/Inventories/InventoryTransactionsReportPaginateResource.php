<?php

namespace App\Http\Resources\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transaction
 */
class InventoryTransactionsReportPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="InventoryTransactionReportRaw",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "quantity", "inventory_id"},
     *             @OA\Property(property="id", type="integer", description="Record id"),
     *             @OA\Property(property="inventory_id", type="integer", description="Inventory id"),
     *             @OA\Property(property="stock_number", type="string", description="Inventory stock number"),
     *             @OA\Property(property="name", type="string", description="Inventory name"),
     *             @OA\Property(property="is_inventory_deleted", type="string", description="Is inventory deleted"),
     *             @OA\Property(property="date", type="integer", description="Date"),
     *             @OA\Property(property="operation_type", type="string", description="Operation type:purchase, sold,reserve"),
     *             @OA\Property(property="quantity", type="number", description="Quantity"),
     *             @OA\Property(property="cost", type="number", description="Cost"),
     *             @OA\Property(property="price", type="number", description="Price"),
     *             @OA\Property(property="invoice_number", type="string", description="Invoice Number"),
     *             @OA\Property(property="category", type="string", description="Inventory category name"),
     *             @OA\Property(property="supplier", type="string", description="Inventory supplier name"),
     *             @OA\Property(property="order_id", type="integer", description="Order id"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="InventoryTransactionReportPaginate",
     *     @OA\Property(
     *         property="data",
     *         description="Inventory report paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/InventoryTransactionReportRaw")
     *     ),
     *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'inventory_id' => $this->inventory_id,
            'stock_number' => $this->inventory->stock_number,
            'name' => $this->inventory->name,
            'is_inventory_deleted' => $this->inventory->trashed(),
            'date' => $this->transaction_date->timestamp,
            'operation_type' => $this->operation_type,
            'quantity' => $this->quantity,
            'cost' => $this->isPurchase() ? $this->price_with_discount_and_tax ?? $this->price : null,
            'price' => $this->isSold() ? $this->price_with_discount_and_tax ?? $this->price : null,
            'invoice_number' => $this->invoice_number,
            'category' => $this->inventory->category->name ?? null,
            'supplier' => $this->inventory->supplier->name ?? null,
            'order_id' => $this->order_id,
        ];
    }
}
