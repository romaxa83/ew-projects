<?php


namespace App\Http\Resources\Carrier;

use App\Http\Resources\Files\FileResource;
use App\Models\Saas\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class UsdotResource extends JsonResource
{

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            Company::USDOT_FIELD_CARRIER => FileResource::make(
                $this->getFirstMedia(Company::USDOT_FIELD_CARRIER)
            ),
        ];
    }

}

/**
 * @OA\Schema(
 *    schema="CarrierUsdotResource",
 *    type="object",
 *        @OA\Property(
 *            property="data",
 *            type="object",
 *            description="Carrier USDOT image link",
 *            allOf={
 *                @OA\Schema(
 *                    required={"usdot_number_image"},
 *                        @OA\Property(property="usdot_number_image", type="object", description="Carrier USDOT", allOf={
 *                              @OA\Schema(ref="#/components/schemas/File")
 *                        }),
 *                )
 *            }
 *        ),
 * )
 */
