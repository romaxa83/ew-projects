<?php

namespace App\Http\Resources\Orders\BS;

use App\Models\Orders\BS\Payment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="PaymentResourceRawBS", type="object", allOf={
 *     @OA\Schema(
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="amount", type="number"),
 *         @OA\Property(property="payment_date", type="integer"),
 *         @OA\Property(property="payment_method", type="string"),
 *         @OA\Property(property="payment_method_name", type="string"),
 *         @OA\Property(property="notes", type="string"),
 *         @OA\Property(property="reference_number", type="string"),
 *     )}
 * )
 *
 * @OA\Schema(schema="PaymentResourceBS", type="object",
 *     @OA\Property(property="data", type="object", allOf={
 *         @OA\Schema(ref="#/components/schemas/PaymentResourceRawBS")
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
            'payment_date' => $this->payment_date->timestamp,
            'payment_method' => $this->payment_method,
            'payment_method_name' => $this->payment_method->label(),
            'notes' => $this->notes,
            'reference_number' => $this->reference_number,
        ];
    }
}
