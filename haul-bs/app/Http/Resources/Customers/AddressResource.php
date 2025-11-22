<?php

namespace App\Http\Resources\Customers;

use App\Models\Customers\Address;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="AddressRawResource", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "phone", "first_name", "last_name", "is_default", "from_ecomm", "address", "city", "state", "zip"},
 *         @OA\Property(property="id", type="integer", description="Customer id"),
 *         @OA\Property(property="is_default", type="string", example="true"),
 *         @OA\Property(property="from_ecomm", type="string", example="false"),
 *         @OA\Property(property="first_name", type="string", example="John"),
 *         @OA\Property(property="last_name", type="string", example="Doe"),
 *         @OA\Property(property="company_name", type="string", example="Sony Inc."),
 *         @OA\Property(property="address", type="string", example="801 West Dundee Road"),
 *         @OA\Property(property="city", type="string", example="Arlington Heights"),
 *         @OA\Property(property="state", type="string", example="CA"),
 *         @OA\Property(property="zip", type="string", example="60004"),
 *         @OA\Property(property="phone", type="string", example="1555555555"),
 *     )}
 * )
 *
 * @OA\Schema(schema="AddressResource",
 *     @OA\Property(property="data", description="Customer address data", type="array",
 *         @OA\Items(ref="#/components/schemas/AddressRawResource")
 *     ),
 * )
 *
 * @mixin Address
 */
class AddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'is_default' => $this->is_default,
            'from_ecomm' => $this->from_ecomm,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company_name' => $this->company_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'phone' => $this->phone->getValue(),
        ];
    }
}
