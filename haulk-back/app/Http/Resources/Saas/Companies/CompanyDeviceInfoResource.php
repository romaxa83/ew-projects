<?php

namespace App\Http\Resources\Saas\Companies;

use App\Entities\Saas\Company\CompanyDeviceInfo;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CompanyDeviceInfo
 */
class CompanyDeviceInfoResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'total_device' => $this->totalDevice,
            'total_active_device' => $this->totalActiveDevice,
            'total_inactive_device' => $this->totalInactiveDevice,
        ];
    }
}

/**
 *  * @OA\Schema(schema="CompanyDeviceInfoResourceRaw", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="total_device", type="integer"),
 *             @OA\Property(property="total_active_device", type="integer"),
 *             @OA\Property(property="total_inactive_device", type="integer"),
 *         )
 *     }
 * )
 *
 *  * @OA\Schema(schema="CompanyDeviceInfoResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/CompanyDeviceInfoResourceRaw")
 *     ),
 * )
 *
 */

