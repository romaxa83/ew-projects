<?php

namespace App\Http\Resources\Customers;

use App\Models\Customers\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="CustomerRawShort", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "phone", "first_name", "last_name", "email"},
 *         @OA\Property(property="id", type="integer", description="Customer id"),
 *         @OA\Property(property="first_name", type="string", description="Customer First Name"),
 *         @OA\Property(property="last_name", type="string", description="Customer Last Name"),
 *         @OA\Property(property="phone", type="string", description="Customer Phone"),
 *         @OA\Property(property="phone_extension", type="string", description="Customer Phone Extension"),
 *         @OA\Property(property="email", type="string", description="Customer Email"),
 *     )}
 * )
 *
 * @OA\Schema(schema="CustomerShortListResource",
 *     @OA\Property(property="data", description="Customer short list", type="array",
 *         @OA\Items(ref="#/components/schemas/CustomerRawShort")
 *     ),
 * )
 *
 * @mixin Customer
 */
class CustomerShortListResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone?->getValue(),
            'phone_extension' => $this->phone_extension,
            'email' => $this->email->getValue(),
        ];
    }
}
