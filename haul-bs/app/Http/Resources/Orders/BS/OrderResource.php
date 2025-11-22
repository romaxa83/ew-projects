<?php

namespace App\Http\Resources\Orders\BS;

use App\Http\Resources\Customers\CustomerShortListResource;
use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Http\Resources\Vehicles\VehicleShortWithTagResource;
use App\Models\Orders\BS\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="OrderBSResource", type="object",
 *     @OA\Property(property="data", type="object", description="Order data", allOf={
 *         @OA\Schema(
 *             required={"id", "order_number", "mechanic", "implementation_date", "status"},
 *             @OA\Property(property="id", type="integer", description="Order id"),
 *             @OA\Property(property="order_number", type="string", description="Order number"),
 *             @OA\Property(property="vehicle", ref="#/components/schemas/VehicleShortWithTagResource"),
 *             @OA\Property(property="discount", type="number", description="Order discount, %"),
 *             @OA\Property(property="tax_labor", type="number", description="Order tax labor, %"),
 *             @OA\Property(property="tax_inventory", type="number", description="Order tax inventory, %"),
 *             @OA\Property(property="implementation_date", type="string", description="Order implementation date, format YYYY-MM-DD HH:mm"),
 *             @OA\Property(property="due_date", type="string", description="Order due date, format YYYY-MM-DD"),
 *             @OA\Property(property="notes", type="string", description="Order notes"),
 *             @OA\Property(property="mechanic", ref="#/components/schemas/UserRawShort"),
 *             @OA\Property(property="customer", ref="#/components/schemas/CustomerRawShort"),
 *             @OA\Property(property="status", type="string", description="Order status"),
 *             @OA\Property(property="payment_status", type="string", description="Order payment status"),
 *             @OA\Property(property="typesOfWork", description="Type Of Work data", type="array",
 *                 @OA\Items(ref="#/components/schemas/OrderTypeOfWork")
 *             ),
 *             @OA\Property(property="total_amount", type="number", description="Order total amount"),
 *             @OA\Property(property="attachments", type="array", description="Order attachments",
 *                 @OA\Items(ref="#/components/schemas/FileRaw")
 *             ),
 *             @OA\Property(property="payments", description="Payments data", type="array",
 *                 @OA\Items(ref="#/components/schemas/PaymentResourceRawBS")
 *             ),
 *             @OA\Property(property="billed_at", type="integer", description="Billed date"),
 *             @OA\Property(property="paid_at", type="integer", description="Paid date"),
 *             @OA\Property(property="status_changed_at", type="integer", description="Date/time when status was changed"),
 *             @OA\Property(property="is_price_changed", type="boolean", description="Is price changed"),
 *         )
 *     }),
 * )
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'vehicle' => VehicleShortWithTagResource::make($this->vehicle),
            'discount' => $this->discount,
            'tax_labor' => $this->tax_labor,
            'tax_inventory' => $this->tax_inventory,
            'implementation_date' => to_bs_timezone($this->implementation_date)->format('Y-m-d H:i'),
            'due_date' => $this->due_date->format('Y-m-d'),
            'notes' => $this->notes,
            'mechanic' => UserShortListResource::make($this->mechanic),
            'customer' => CustomerShortListResource::make($this->vehicle->customer),
            'types_of_work' => $this->typesOfWork ? OrderTypeOfWorkResource::collection($this->typesOfWork) : null,
            'status' => $this->status->value,
            'payment_status' => $this->getCurrentPaymentStatus(),
            'total_amount' => $this->total_amount,
            'is_prices_changed' => $this->isPricesChanged(),
            'payments' => OrderPaymentResource::collection($this->payments),
            'billed_at' => $this->billed_at?->timestamp,
            'paid_at' => $this->paid_at?->timestamp,
            'status_changed_at' => $this->status_changed_at?->timestamp,
            Order::ATTACHMENT_COLLECTION_NAME => FileResource::collection($this->getAttachments()),
        ];
    }
}
