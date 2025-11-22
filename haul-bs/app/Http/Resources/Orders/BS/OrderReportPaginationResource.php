<?php

namespace App\Http\Resources\Orders\BS;

use App\Models\Orders\BS\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="OrderBSReportRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Order report data", allOf={
 *         @OA\Schema(
 *             required={"id", "order_numner", "implementation_date", "mechanic", "status"},
 *             @OA\Property(property="id", type="integer", description="Supplier id"),
 *             @OA\Property(property="order_number", type="string", description="Order number"),
 *             @OA\Property(property="total_amount", type="number", description="Order total amount"),
 *             @OA\Property(property="customer", type="string", description="Order customer name"),
 *             @OA\Property(property="customer_id", type="integer", description="Order customer id"),
 *             @OA\Property(property="implementation_date", type="integer", description="Order implementation date"),
 *             @OA\Property(property="status", type="string", description="Order status"),
 *             @OA\Property(property="is_paid", type="boolean", description="Order is paid"),
 *             @OA\Property(property="current_due", type="number", description="Order current due"),
 *             @OA\Property(property="past_due", type="number", description="Order past due"),
 *             @OA\Property(property="total_due", type="number", description="Order total due"),
 *             @OA\Property(property="parts_cost", type="number", description="Order parts cost"),
 *             @OA\Property(property="profit", type="number", description="Order profit"),
 *         )
 *     }),
 * )
 *
 * @OA\Schema(schema="OrderBSReportPagination",
 *     @OA\Property(property="data", description="Order report paginated list", type="array",
 *         @OA\Items(ref="#/components/schemas/OrderBSReportRaw")
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 * )
 *
 * @mixin Order
 */
class OrderReportPaginationResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'implementation_date' => $this->implementation_date->timestamp,
            'customer' => $this?->vehicle?->customer->full_name,
            'customer_id' => $this?->vehicle->customer_id,
            'status' => $this->status->value,
            'is_paid' => $this->is_paid,
            'current_due' => $this->getCurrentDue(),
            'past_due' => $this->getPastDue(),
            'total_due' => $this->debt_amount,
            'parts_cost' => number_format($this->parts_cost, 2, '.', ''),
            'profit' => number_format($this->profit, 2, '.', '')
        ];
    }
}
