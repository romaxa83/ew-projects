<?php

namespace App\Http\Resources\Usdot;

use App\Entities\Usdot\CarrierInfo;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CarrierInfo $resource
 */
class UsdotApiResource extends JsonResource
{
    public function toArray($request): array
    {
        $carrierInfo = $this->resource;

        return [
            'usdot' => $carrierInfo->getDotNumber(),
            'mc_number' => $carrierInfo->getMcNumber(),
            'name' => $carrierInfo->getName(),
            'city' => $carrierInfo->getCity(),
            'address' => $carrierInfo->getAddress(),
            'zip' => $carrierInfo->getZip(),
            'state' => $carrierInfo->getState(),
            'status' => $carrierInfo->getStatus(),
        ];
    }
}
/**
 * @OA\Schema(schema="UsdotApiResource", type="object", allOf={
 *            @OA\Schema(
 *                @OA\Property(property="usdot", type="integer"),
 *                @OA\Property(property="mc_number", type="integer"),
 *                @OA\Property(property="name", type="string"),
 *                @OA\Property(property="city", type="string"),
 *                @OA\Property(property="address", type="string"),
 *                @OA\Property(property="zip", type="string"),
 *                @OA\Property(property="state", type="string"),
 *                @OA\Property(property="status", type="string"),
 *            )
 *        }
 * )
 */
