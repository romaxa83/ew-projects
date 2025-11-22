<?php

namespace App\Http\Resources\Saas\Companies;

use App\Http\Resources\Locations\StateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="CompanyInfoResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        description="Carrier profile data",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="name", type="string", description="Carrier name"),
     *                @OA\Property(property="usdot", type="string", description="Carrier usdot"),
     *                @OA\Property(property="ga_id", type="string", description="Carrier ga_id"),
     *                @OA\Property(property="address", type="string", description="Carrier address"),
     *                @OA\Property(property="city", type="string", description="Carrier city"),
     *                @OA\Property(property="state", type="object", description="Carrier state",
     *                    allOf={
     *                        @OA\Schema(ref="#/components/schemas/StateRaw")
     *                    }
     *                ),
     *                @OA\Property(property="state_id", type="integer", description="Carrier state id"),
     *                @OA\Property(property="zip", type="string", description="Carrier zip"),
     *                @OA\Property(property="phone", type="string", description="Carrier phone"),
     *                @OA\Property(property="phone_name", type="string", description="Carrier contact name"),
     *                @OA\Property(property="phones", type="array", description="Carrier phones",
     *                    @OA\Items(
     *                        type="object",
     *                        allOf={
     *                            @OA\Schema(
     *                                @OA\Property(property="name", type="string", description="Contact person name"),
     *                                @OA\Property(property="number", type="string", description="Phone number"),
     *                            )
     *                        }
     *                    ),
     *                ),
     *                @OA\Property(property="email", type="string", description="Carrier email"),
     *            )
     *        }
     *    ),
     * )
     */
    public function toArray($request): array
    {
        return [
            'usdot' => $this->usdot,
            'ga_id' => $this->ga_id,
            'mc_number' => $this->mc_number,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => StateResource::make($this->state),
            'state_id' => $this->state_id,
            'zip' => $this->zip,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_name' => $this->phone_name,
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones,
            'payment_details' => $this->billingInfo->billing_payment_details,
        ];
    }
}
