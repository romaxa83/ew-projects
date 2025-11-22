<?php


namespace App\Http\Resources\Orders;


use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodMobileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @OA\Schema(
     *    schema="PaymentMethodMobileResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer", description="Payment method id"),
     *                @OA\Property(property="title", type="string", description="Payment method title"),
     *            )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="PaymentMethodMobileResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Payment method data",
     *            allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer", description="Payment method id"),
     *                @OA\Property(property="title", type="string", description="Payment method title"),
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
        ];
    }
}
