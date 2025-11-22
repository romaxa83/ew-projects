<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="OrderPaymentMethodResource", type="object",
 *     @OA\Property(property="data", type="array", description="Payment method data",
 *         @OA\Items(allOf={
 *             @OA\Schema(
 *                 @OA\Property(property="key", type="string",),
 *                 @OA\Property(property="title", type="string",),
 *             )
 *         })
 *     ),
 * )
 *
 * @return array
 */
class PaymentMethodResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'key' => $this['key'],
            'title' => $this['title'],
        ];
    }
}
