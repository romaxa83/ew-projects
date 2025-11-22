<?php

namespace App\Foundations\Http\Resources\Common\Locations;

use App\Foundations\Entities\Locations\AddressEntity;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="AddressCommonRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"first_name", "last_name", "address", "city", "state", "zip", "phone"},
 *         @OA\Property(property="first_name", type="string", example="John"),
 *         @OA\Property(property="last_name", type="string", example="Doe"),
 *         @OA\Property(property="company", type="string", example="Sony Inc."),
 *         @OA\Property(property="address", type="string", example="801 West Dundee Road"),
 *         @OA\Property(property="city", type="string", example="Arlington Heights"),
 *         @OA\Property(property="state", type="string", example="CA"),
 *         @OA\Property(property="zip", type="string", example="60004"),
 *         @OA\Property(property="phone", type="string", example="1555555555"),
 *         @OA\Property(property="customer_address_id", type="integer", example="1"),
 *     )
 * })
 *
 * @mixin AddressEntity
 */
class AddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'phone' => $this->phone->getValue(),
            'save' => $this->save ?? false,
            'customer_address_id' => $this->customer_address_id ?? null,
        ];
    }
}
