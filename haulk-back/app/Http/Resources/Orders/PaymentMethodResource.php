<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @OA\Schema(
     *    schema="PaymentMethodResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer",),
     *                @OA\Property(property="title", type="string",),
     *                @OA\Property(property="available_for", type="array", @OA\Items(type="string")),
     *            )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="PaymentMethodResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Payment method data",
     *            allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer",),
     *                @OA\Property(property="title", type="string",),
     *                @OA\Property(property="available_for", type="array", @OA\Items(type="string")),
     *            )
     *        }
     *        ),
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'available_for' => $this->available_for,
        ];
    }
}
