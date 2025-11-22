<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionInfoMobileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="SubscriptionInfoMobileResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="subscription_active", type="boolean",),
     *            )
     *        }
     *    ),
     * )
     */
    public function toArray($request)
    {
        return [
            'subscription_active' => $this->isSubscriptionActive(),
        ];
    }
}
