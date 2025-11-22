<?php

namespace App\Http\Resources\Saas\Companies;

use App\Models\Saas\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CompanyShortlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}

/**
 *  * @OA\Schema(schema="CompanyShortlistResourceRaw", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *         )
 *     }
 * )
 *
 *  * @OA\Schema(schema="CompanyShortlistResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/CompanyShortlistResourceRaw")
 *     ),
 * )
 *
 */
