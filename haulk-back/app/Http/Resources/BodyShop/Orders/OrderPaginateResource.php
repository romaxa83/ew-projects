<?php

namespace App\Http\Resources\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="OrderRawBS",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Order data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "order_numner", "implementation_date", "mechanic", "status"},
     *                     @OA\Property(property="id", type="integer", description="Supplier id"),
     *                     @OA\Property(property="order_number", type="string", description="Order number"),
     *                     @OA\Property(property="implementation_date", type="string", description="Order implementation date, format YYYY-MM-DD HH:mm"),
     *                     @OA\Property(property="mechanic", type="string", description="Order mechanic name"),
     *                     @OA\Property(property="customer", type="string", description="Order customer name"),
     *                     @OA\Property(property="vehicle", type="object", description="Order vehicle",
     *                         allOf={
     *                             @OA\Schema(
     *                                 required={"id", "make", "model", "year", "vehicle_form"},
     *                                 @OA\Property(property="id", type="integer", description="Vehicle Id"),
     *                                 @OA\Property(property="make", type="string", description="Vehicle Make"),
     *                                 @OA\Property(property="model", type="string", description="Vehicle Model"),
     *                                 @OA\Property(property="year", type="string", description="Vehicle Year"),
     *                                 @OA\Property(property="vehicle_form", type="string", description="Vehicle Form"),
     *                                 @OA\Property(property="vin", type="string", description="Vin"),
     *                                 @OA\Property(property="unit_number", type="string", description="Unit nunber"),
     *                             )
     *                     }),
     *                     @OA\Property(property="status", type="string", description="Order status"),
     *                     @OA\Property(property="payment_status", type="string", description="Order payment status"),
     *                     @OA\Property(property="total_amount", type="number", description="Order total amount"),
     *                     @OA\Property(property="comments_count", type="integer", description="Order comments count"),
     *                     @OA\Property(property="is_overdue", type="boolean", description="Order status"),
     *                     @OA\Property(property="overdue_days", type="integer", description="Overdue days"),
     *                     @OA\Property(property="billed_at", type="integer", description="Billed date"),
     *                     @OA\Property(property="paid_at", type="integer", description="Paid date"),
     *                 )
     *             }
     *         ),
     * )
     *
     * @OA\Schema(
     *     schema="OrderPaginateBS",
     *     @OA\Property(
     *         property="data",
     *         description="Order paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/OrderRawBS")
     *     ),
     *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        $vehicle = $this->truck ?? $this->trailer;

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'vehicle' => $vehicle ? [
                'id' => $vehicle->id,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'vin' => $vehicle->vin,
                'unit_number' => $vehicle->unit_number,
                'vehicle_form' => ($vehicle instanceof Truck) ? Vehicle::VEHICLE_FORM_TRUCK : Vehicle::VEHICLE_FORM_TRAILER,
            ] : null,
            'customer' => $vehicle ? $vehicle->getOwnerFullName() : null,
            'implementation_date' => toBSTimezone($this->implementation_date)->format('Y-m-d H:i'),
            'mechanic' => $this->mechanic->full_name,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'comments_count' => $this->comments()->count(),
            'payment_status' => $this->getCurrentPaymentStatus(),
            'is_overdue' => $this->isOverdue(),
            'overdue_days' => $this->getOverdueDays(),
            'billed_at' => $this->billed_at->timestamp ?? null,
            'paid_at' => $this->paid_at->timestamp ?? null,
        ];
    }
}
