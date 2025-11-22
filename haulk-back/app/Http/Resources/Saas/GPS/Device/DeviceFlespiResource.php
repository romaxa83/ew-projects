<?php

namespace App\Http\Resources\Saas\GPS\Device;

use App\Services\Saas\GPS\Flespi\Entities\DeviceEntity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeviceEntity
 */
class DeviceFlespiResource extends JsonResource
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
            'imei' => $this->imei,
            'phone' => $this->phone,
        ];
    }
}

/**
 *
 * @OA\Schema(schema="DeviceFlespiResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer", description="Device id inside the flespi service"),
 *             @OA\Property(property="name", type="string", description="Device name inside the flespi service"),
 *             @OA\Property(property="imei", type="string", description="Device imei inside the flespi service"),
 *             @OA\Property(property="phone", type="string", description="Device phone inside the flespi service")
 *         )
 *     }
 * )
 *
 */

