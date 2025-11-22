<?php

namespace App\Http\Resources\Vehicles;

use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Device
 */
class GpsDeviceResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'imei' => $this->imei,
        ];
    }
}

/**
 * @OA\Schema(schema="GPSDeviceCRMRawResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="imei", type="string"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="GPSDeviceCRMListResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/GPSDeviceCRMRawResource")
 *     ),
 * )
 */
