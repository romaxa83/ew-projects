<?php

namespace App\Http\Resources\Customers;

use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Vehicles\VehicleForCustomerResource;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="CustomerTaxExemptionResource", type="object",
 *     @OA\Property(property="data", type="object", description="Customer data", allOf={
 *         @OA\Schema(
 *             required={"id", "fisrt_name", "last_name", "phone", "email"},
 *             @OA\Property(property="id", type="integer", description="id"),
 *             @OA\Property(property="date_active_to", type="integer", description="дата до которого действует"),
 *             @OA\Property(property="status", type="string", enum={"NOT_SEND", "UNDER_REVIEW", "ACCEPTED", "DECLINED", "EXPIRED"}),
 *             @OA\Property(property="type", type="string", enum={"ECOM", "BODY"}),
 *             @OA\Property(property="link", type="string", description="link"),
 *             @OA\Property(property="file_name", type="string", description="file_name"),
 *             @OA\Property(property="file", ref="#/components/schemas/FileRaw")
 *         )}
 *     ),
 * )
 *
 * @mixin CustomerTaxExemption
 */
class CustomerTaxExemptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date_active_to' => $this->date_active_to?->timestamp,
            'status' => $this->status,
            'type' => $this->type,
            'link' => $this->link,
            'file_name' => $this->file_name,
            'file' => FileResource::make($this->file),
        ];
    }
}
