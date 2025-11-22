<?php

namespace App\Http\Resources\BodyShop\Orders;

use App\Http\Resources\BodyShop\VehicleOwners\VehicleOwnerShortResource;
use App\Http\Resources\BodyShop\Vehicles\Trailers\TrailerShortResource;
use App\Http\Resources\BodyShop\Vehicles\Trucks\TruckShortResource;
use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Users\UserShortResource;
use App\Models\BodyShop\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="OrderBS",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Order data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "order_number", "mechanic", "implementation_date", "status"},
     *                     @OA\Property(property="id", type="integer", description="Order id"),
     *                     @OA\Property(property="order_number", type="string", description="Order number"),
     *                     @OA\Property(property="vehicle", ref="#/components/schemas/TruckShortBS"),
     *                     @OA\Property(property="discount", type="number", description="Order discount, %"),
     *                     @OA\Property(property="tax_labor", type="number", description="Order tax labor, %"),
     *                     @OA\Property(property="tax_inventory", type="number", description="Order tax inventory, %"),
     *                     @OA\Property(property="implementation_date", type="string", description="Order implementation date, format YYYY-MM-DD HH:mm"),
     *                     @OA\Property(property="due_date", type="string", description="Order due date, format YYYY-MM-DD"),
     *                     @OA\Property(property="notes", type="string", description="Order notes"),
     *                     @OA\Property(property="mechanic", ref="#/components/schemas/UserShort"),
     *                     @OA\Property(property="customer", ref="#/components/schemas/VehicleOwnerShort"),
     *                     @OA\Property(property="status", type="string", description="Order status"),
     *                     @OA\Property(property="payment_status", type="string", description="Order payment status"),
     *                     @OA\Property(
     *                         property="typesOfWork",
     *                         description="Type Of Work data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/OrderTypeOfWork")
     *                     ),
     *                     @OA\Property(property="total_amount", type="number", description="Order total amount"),
     *                     @OA\Property(property="attachments", type="array", description="Order attachments", @OA\Items(ref="#/components/schemas/FileRaw")),
     *                     @OA\Property(
     *                         property="payments",
     *                         description="Payments data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/PaymentResourceRawBS")
     *                     ),
     *                     @OA\Property(property="billed_at", type="integer", description="Billed date"),
     *                     @OA\Property(property="paid_at", type="integer", description="Paid date"),
     *                     @OA\Property(property="status_changed_at", type="integer", description="Date/time when status was changed"),
     *                     @OA\Property(property="is_price_changed", type="boolean", description="Is price changed"),
     *                 )
     *             }
     *         ),
     * )
     */
    public function toArray($request)
    {
        $vehicle = $this->truck ?? $this->trailer;
        $customerResource = ($vehicle && $vehicle->owner) ? UserShortResource::make($vehicle->owner) : null;
        if (is_null($customerResource) && $vehicle) {
            $customerResource = $vehicle->customer ? VehicleOwnerShortResource::make($vehicle->customer) : null;
        }

        $vehicleResource = $this->truck ? TruckShortResource::make($this->truck) : null;

        if (!$vehicleResource) {
            $vehicleResource = $this->trailer ? TrailerShortResource::make($this->trailer) : null;
        }

        $partsCost = round($this->getPartsCost(), 2);

//        dd($partsCost);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'vehicle' => $vehicleResource,
            'discount' => $this->discount,
            'tax_labor' => $this->tax_labor,
            'tax_inventory' => $this->tax_inventory,
            'implementation_date' => toBSTimezone($this->implementation_date)->format('Y-m-d H:i'),
            'due_date' => $this->due_date->format('Y-m-d'),
            'notes' => $this->notes,
            'mechanic' => UserShortResource::make($this->mechanic),
            'customer' => $customerResource,
            'types_of_work' => $this->typesOfWork ? OrderTypeOfWorkResource::collection($this->typesOfWork) : null,
            'status' => $this->status,
            'payment_status' => $this->getCurrentPaymentStatus(),
            'total_amount' => $this->total_amount,
            Order::ATTACHMENT_COLLECTION_NAME => FileResource::collection($this->getAttachments()),
            'is_prices_changed' => $this->isPricesChanged(),
            'payments' => PaymentResource::collection($this->payments),
            'billed_at' => $this->billed_at->timestamp ?? null,
            'paid_at' => $this->paid_at->timestamp ?? null,
            'status_changed_at' => $this->status_changed_at->timestamp ?? null,
            'parts_cost' => number_format($this->parts_cost, 2, '.', ''),
            'profit' => number_format($this->profit, 2, '.', '')
//            'parts_cost' => number_format($partsCost, 2, '.', ''),
//            'profit' => $partsCost
//                ? number_format(round($this->total_amount - $partsCost, 2), 2, '.', '')
//                : number_format($partsCost, 2, '.', '')
        ];
    }
}
