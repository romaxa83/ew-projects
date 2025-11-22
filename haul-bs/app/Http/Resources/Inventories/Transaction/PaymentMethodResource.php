<?php

namespace App\Http\Resources\Inventories\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="PaymentMethodResource", type="object",
 *     @OA\Property(property="data", type="array", description="Payment method data",
 *         @OA\Items(allOf={
 *             @OA\Schema(
 *                 @OA\Property(property="key", type="string",),
 *                 @OA\Property(property="title", type="string",),
 *             )
 *         })
 *     ),
 * )
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

