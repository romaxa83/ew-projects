<?php

namespace App\Http\Resources\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transaction
 */
class TransactionPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="InventoryTransactionRaw",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "cost", "quantity", "inventory_id"},
     *             @OA\Property(property="id", type="integer", description="Record id"),
     *             @OA\Property(property="operation_type", type="string", description="Operation type:purchase, sold,reserve"),
     *             @OA\Property(property="quantity", type="number", description="Quantity"),
     *             @OA\Property(property="price", type="number", description="Cost/Price"),
     *             @OA\Property(property="invoice_number", type="string", description="Invoice Number"),
     *             @OA\Property(property="inventory_id", type="integer", description="Inventory id"),
     *             @OA\Property(property="order_id", type="integer", description="Order id"),
     *             @OA\Property(property="comment", type="string", description="Comment"),
     *             @OA\Property(property="date", type="integer", description="Date"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="InventoryTransactionPaginate",
     *     @OA\Property(
     *         property="data",
     *         description="Inventory paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/InventoryTransactionRaw")
     *     ),
     *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'operation_type' => $this->operation_type,
            'quantity' => $this->quantity,
            'price' => $this->price_with_discount_and_tax ?? $this->price,
            'invoice_number' => $this->invoice_number,
            'inventory_id' => $this->inventory_id,
            'order_id' => $this->order_id,
            'comment' => $this->describe,
            'date' => $this->transaction_date->timestamp,
        ];
    }
}
