<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Resources\Json\JsonResource;

class BillingInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="BillingInfoResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="billing_contact", type="object", allOf={
     *                    @OA\Schema(
     *                        @OA\Property(property="full_name", type="string",),
     *                        @OA\Property(property="email", type="string",),
     *                        @OA\Property(property="use_accounting_contact", type="boolean",),
     *                    )
     *                }),
     *                @OA\Property(property="payment_history", type="array", @OA\Items(
     *                    allOf={
     *                        @OA\Schema(
     *                            @OA\Property(property="id", type="integer",),
     *                            @OA\Property(property="date", type="integer",),
     *                            @OA\Property(property="price", type="number",),
     *                        )
     *                    }
     *                )),
     *                @OA\Property(property="estimated_payment", type="object", allOf={
     *                    @OA\Schema(
     *                        @OA\Property(property="price", type="number",),
     *                        @OA\Property(property="driver_count", type="integer",),
     *                        @OA\Property(property="next_payment_date", type="integer",),
     *                        @OA\Property(property="device_price", type="integer",),
     *                        @OA\Property(property="driver_price", type="integer",),
     *                    )
     *                }),
     *                @OA\Property(property="information", type="string",),
     *                @OA\Property(property="notice", type="string",),
     *                @OA\Property(property="payment_method", type="object", allOf={
     *                    @OA\Schema(
     *                        @OA\Property(property="full_name", type="string",),
     *                        @OA\Property(property="card_number", type="string",),
     *                        @OA\Property(property="expires_at", type="string",),
     *                    )
     *                }),
     *            )
     *        }
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
