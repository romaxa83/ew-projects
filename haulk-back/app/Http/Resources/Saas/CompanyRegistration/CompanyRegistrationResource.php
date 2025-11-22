<?php

namespace App\Http\Resources\Saas\CompanyRegistration;

use App\Http\Resources\Locations\StateResource;
use App\Models\Saas\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyRegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'usdot' => $this->usdot,
            'ga_id' => $this->ga_id,
            'mc_number' => $this->mc_number,
            'type' => 'carrier',
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => StateResource::make($this->state),
            'zip' => $this->zip,
            'status' => Company::getCompanyStatusByCode($this->status),
            'created_at' => $this->created_at->timestamp,
        ];
    }
}

/**
 *
 * @OA\Schema(schema="CompanyRegistrationResourceRaw", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="usdot", type="integer"),
 *             @OA\Property(property="ga_id", type="string"),
 *             @OA\Property(property="mc_number", type="integer"),
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="first_name", type="string"),
 *             @OA\Property(property="last_name", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="phone", type="string"),
 *             @OA\Property(property="address", type="string"),
 *             @OA\Property(property="city", type="string"),
 *             @OA\Property(property="state", ref="#/components/schemas/StateRaw",),
 *             @OA\Property(property="zip", type="string"),
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(property="created_at", type="integer"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="CompanyRegistrationResource", type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/CompanyRegistrationResourceRaw")
 *         }
 *     )
 * )
 *
 */
