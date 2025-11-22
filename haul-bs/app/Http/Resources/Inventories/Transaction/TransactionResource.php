<?php

namespace App\Http\Resources\Inventories\Transaction;

use App\Models\Inventories\Transaction;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryTransactionResource", type="object",
 *     @OA\Property(property="data", type="object", description="Inventory transaction data", allOf={
 *         @OA\Schema(
 *             required={"id", "cost", "quantity", "inventory_id"},
 *             @OA\Property(property="id", type="integer", description="Record id"),
 *             @OA\Property(property="operation_type", type="string", description="Operation type:purchase, sold,reserve"),
 *             @OA\Property(property="quantity", type="number", description="Quantity"),
 *             @OA\Property(property="cost", type="number", description="Cost/Price"),
 *             @OA\Property(property="invoice_number", type="string", description="Invoice Number"),
 *             @OA\Property(property="inventory_id", type="integer", description="Inventory id"),
 *             @OA\Property(property="order_id", type="integer", description="Order id"),
 *             @OA\Property(property="order_type", type="string", description="Order type",
 *                 enum={"bs", "parts"}
 *             ),
 *             @OA\Property(property="describe", type="string", description="Describe"),
 *             @OA\Property(property="date", type="integer", description="Date"),
 *             @OA\Property(property="payment_date", type="integer", description="Payment Date"),
 *             @OA\Property(property="payment_method", type="integer", description="Payment method"),
 *             @OA\Property(property="tax", type="number", description="Tax"),
 *             @OA\Property(property="discount", type="number", description="Discount"),
 *             @OA\Property(property="first_name", type="string", description="First name"),
 *             @OA\Property(property="last_name", type="string", description="Last name"),
 *             @OA\Property(property="company_name", type="string", description="Company name"),
 *             @OA\Property(property="phone", type="string", description="Phone"),
 *             @OA\Property(property="email", type="string", description="Email"),
 *         )}
 *     ),
 * )
 *
 * @mixin Transaction
 */
class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var $model Transaction */
        $model = $this->resource;

        return [
            'id' => $this->id,
            'operation_type' => $this->operation_type->value,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'invoice_number' => $this->invoice_number,
            'inventory_id' => $this->inventory_id,
            'order_id' => TransactionResource::getOrderId($model),
            'order_type' => $this->order_type,
            'describe' => $this->describe,
            'date' => $this->transaction_date->timestamp,
            'payment_date' => $this->payment_date ?? null,
            'payment_method' => $this->payment_method,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company_name' => $this->company_name,
            'phone' => $this->phone?->getValue(),
            'email' => $this->email?->getValue(),
            'total_amount' => $this->getTotalAmount(),
        ];
    }

    public static function getOrderId (Transaction $self): ?int
    {
        $orderId = null;
        if($self->order_type?->isBs()){
            $orderId = $self->order_id;
        }
        if($self->order_type?->isParts()){
            $orderId = $self->order_parts_id;
        }

        return $orderId;
    }
}
