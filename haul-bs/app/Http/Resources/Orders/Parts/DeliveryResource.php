<?php

namespace App\Http\Resources\Orders\Parts;

use App\Models\Orders\Parts\Delivery;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="DeliveryRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"id", "delivery_method", "delivery_cost"},
 *          @OA\Property(property="id", type="integer", description="Delivery id"),
 *          @OA\Property(property="status", type="string", description="Delivery status",
 *              enum={"sent", "delivered"}
 *          ),
 *          @OA\Property(property="delivery_method", type="string", description="Delivery method"),
 *          @OA\Property(property="delivery_cost", type="number", description="Delivery cost"),
 *          @OA\Property(property="date_sent", type="string", description="Sent date"),
 *          @OA\Property(property="tracking_number", type="string", description="Tracking number"),
 *      )
 * })
 *
 * @OA\Schema(schema="DeliveryListResource",
 *     @OA\Property(property="data", description="Delivery list", type="array",
 *         @OA\Items(ref="#/components/schemas/DeliveryRaw")
 *     ),
 * )
 *
 * @mixin Delivery
 */
class DeliveryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'delivery_method' => $this->method->value,
            'delivery_cost' => $this->cost,
            'date_sent' => $this->sent_at?->timestamp,
            'tracking_number' => $this->tracking_number,
        ];
    }
}
