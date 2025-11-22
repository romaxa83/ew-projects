<?php

namespace App\Http\Resources\Saas\CompanyRegistration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyRegistrationPaginatedResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'created_at' => $this->created_at->timestamp,
        ];
    }
}

/**
 * @OA\Schema(schema="CompanyRegistrationPaginatedResourceRaw", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="usdot", type="integer"),
 *             @OA\Property(property="ga_id", type="string"),
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="first_name", type="string"),
 *             @OA\Property(property="last_name", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="phone", type="string"),
 *             @OA\Property(property="created_at", type="integer"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="CompanyRegistrationPaginatedResource",
 *     @OA\Property(property="data", type="array",
 *         @OA\Items(ref="#/components/schemas/CompanyRegistrationPaginatedResourceRaw")
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 *
 */
