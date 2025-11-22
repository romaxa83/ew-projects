<?php

namespace App\Http\Resources\BodyShop\Orders;

use App\Models\BodyShop\Orders\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="PaymentResourceRawBS",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer"),
     *            @OA\Property(property="amount", type="number"),
     *            @OA\Property(property="payment_date", type="integer"),
     *            @OA\Property(property="payment_method", type="string"),
     *            @OA\Property(property="payment_method_name", type="string"),
     *            @OA\Property(property="notes", type="string"),
     *            @OA\Property(property="reference_number", type="string"),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="PaymentResourceBS",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        allOf={
     *            @OA\Schema(ref="#/components/schemas/PaymentResourceRawBS")
     *        }
     *    )
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date->timestamp,
            'payment_method' => $this->payment_method,
            'payment_method_name' => Payment::PAYMENT_METHODS[$this->payment_method],
            'notes' => $this->notes,
            'reference_number' => $this->reference_number,
        ];
    }
}
