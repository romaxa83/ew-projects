<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderAvailableInvoicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="OrderAvailableInvocesResource",
     *    type="array",
     *    @OA\Items (ref="#/components/schemas/OrderAvailableInvoceResource")
     * )
     *
     * @OA\Schema (
     *     type="object",
     *     required={"amount","recipient"},
     *     schema="OrderAvailableInvoceResource",
     *     @OA\Property (
     *         property="amount",
     *         description="Invoice amount",
     *         type="number",
     *         nullable=false
     *     ),
     *     @OA\Property (
     *         property="recipient",
     *         description="Invoice recipient",
     *         type="string",
     *         nullable=false,
     *         enum={"broker", "customer"}
     *     ),
     * )
     *
     */
    public function toArray($request): array
    {
        return $this->resource;
    }

}
