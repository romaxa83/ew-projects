<?php

namespace App\Http\Resources\Orders\BS;

use App\Http\Resources\Vehicles\VehicleShortResource;
use App\Models\Orders\BS\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="OrderBSRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Order bodyshop data", allOf={
 *         @OA\Schema(
 *             required={"id", "order_numner", "implementation_date", "mechanic", "status"},
 *             @OA\Property(property="id", type="integer", description="Supplier id"),
 *             @OA\Property(property="order_number", type="string", description="Order number"),
 *             @OA\Property(property="implementation_date", type="string", description="Order implementation date, format YYYY-MM-DD HH:mm"),
 *             @OA\Property(property="mechanic", type="string", description="Order mechanic name"),
 *             @OA\Property(property="customer", type="string", description="Order customer name"),
 *             @OA\Property(property="vehicle", type="object", ref="#/components/schemas/VehicleShortRaw"),
 *             @OA\Property(property="status", type="string", description="Order status"),
 *             @OA\Property(property="payment_status", type="string", description="Order payment status"),
 *             @OA\Property(property="total_amount", type="number", description="Order total amount"),
 *             @OA\Property(property="comments_count", type="integer", description="Order comments count"),
 *             @OA\Property(property="is_overdue", type="boolean", description="Order status"),
 *             @OA\Property(property="overdue_days", type="integer", description="Overdue days"),
 *             @OA\Property(property="billed_at", type="integer", description="Billed date"),
 *             @OA\Property(property="paid_at", type="integer", description="Paid date"),
 *         )
 *     }),
 * )
 *
 * @OA\Schema(schema="OrderBSPaginationResource",
 *     @OA\Property(property="data", description="Order bodyshop paginated list", type="array",
 *         @OA\Items(ref="#/components/schemas/OrderBSRaw")
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 * )
 *
 * @mixin Order
 */
class OrderPaginationResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'vehicle' => VehicleShortResource::make($this->vehicle),
            'customer' => $this->vehicle?->customer->full_name,
            'implementation_date' => to_bs_timezone($this->implementation_date)->format('Y-m-d H:i'),
            'mechanic' => $this->mechanic->full_name,
            'total_amount' => $this->total_amount,
            'status' => $this->status->value,
            'notes' => $this->notes,
            'comments_count' => $this->comments->count(),
            'payment_status' => $this->getCurrentPaymentStatus(),
            'is_overdue' => $this->isOverdue(),
            'overdue_days' => $this->getOverdueDays(),
            'billed_at' => $this->billed_at?->timestamp,
            'paid_at' => $this->paid_at?->timestamp,
        ];
    }
}
