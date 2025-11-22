<?php

namespace App\Http\Resources\Orders\Parts;

use App\Models\Orders\Parts\Payment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="PaymentResourceRawParts", type="object", allOf={
 *     @OA\Schema(
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="amount", type="number"),
 *         @OA\Property(property="payment_date", type="integer"),
 *         @OA\Property(property="payment_method", type="string"),
 *         @OA\Property(property="payment_method_name", type="string"),
 *         @OA\Property(property="notes", type="string"),
 *     )}
 * )
 *
 * @OA\Schema(schema="PaymentResourceParts", type="object",
 *     @OA\Property(property="data", type="object", allOf={
 *         @OA\Schema(ref="#/components/schemas/PaymentResourceRawParts")
 *     })
 * )
 *
 * @mixin Payment
 */
class OrderPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'payment_date' => $this->payment_at->timestamp,
            'payment_method' => $this->payment_method,
            'payment_method_name' => $this->payment_method->label(),
            'notes' => $this->notes,
        ];
    }
}
