<?php

namespace App\Http\Resources\BodyShop\Companies;

use App\Models\Saas\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CompanyListResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="CompanyBSRaw",
     *     type="object",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "name"},
     *                     @OA\Property(property="id", type="integer", description="Inventory category id"),
     *                     @OA\Property(property="name", type="string", description="Inventory category Name"),
     *                 )
     *             }
     * )
     *
     * @OA\Schema(
     *     schema="CompaniesBSList",
     *     @OA\Property(
     *         property="data",
     *         description="Companies list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/CompanyBSRaw")
     *     ),
     * )
     */

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
