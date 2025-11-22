<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoicePaginatedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="InvoicePaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        @OA\Items(
     *            allOf={
     *                @OA\Schema(
     *                    @OA\Property(property="id", type="integer",),
     *                    @OA\Property(property="company_name", type="string",),
     *                    @OA\Property(property="billing_start", type="integer",),
     *                    @OA\Property(property="billing_end", type="integer",),
     *                    @OA\Property(property="amount", type="float",),
     *                    @OA\Property(property="trans_id", type="string",),
     *                    @OA\Property(property="pending", type="boolean",),
     *                    @OA\Property(property="is_paid", type="boolean",),
     *                    @OA\Property(property="paid_at", type="integer",),
     *                    @OA\Property(property="public_token", type="string",),
     *                    @OA\Property(property="attempt", type="integer"),
     *                )
     *            }
     *        )
     *    ),
     *    @OA\Property(
     *        property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property(
     *        property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'billing_start' => strtotime($this->billing_start),
            'billing_end' => strtotime($this->billing_end),
            'amount' => $this->amount,
            'trans_id' => $this->trans_id,
            'pending' => $this->pending,
            'is_paid' => $this->is_paid,
            'paid_at' => $this->paid_at,
            'public_token' => $this->public_token,
            'attempt' => $this->attempt,
        ];
    }
}
