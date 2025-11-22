<?php

namespace App\Http\Resources\BodyShop\Orders;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="PaymentMethodResourceBS",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="array",
     *            description="Payment method data",
     *            @OA\Items(
     *                allOf={
     *                    @OA\Schema(
     *                        @OA\Property(property="key", type="string",),
     *                        @OA\Property(property="title", type="string",),
     *                    )
     *                }
     *            )
     *        ),
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'key' => $this['key'],
            'title' => $this['title'],
        ];
    }
}
