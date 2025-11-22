<?php

namespace App\Http\Resources\Saas\Invoices;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="InvoiceCompanyListResource",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        @OA\Items(
     *            allOf={
     *                @OA\Schema(
     *                    @OA\Property(property="company_id", type="integer",),
     *                    @OA\Property(property="company_name", type="string",)
     *                )
     *            }
     *        )
     *    )
     * )
     */
    public function toArray($request): array
    {
        return [
            'company_id' => $this->carrier_id,
            'company_name' => $this->company_name
        ];
    }
}
