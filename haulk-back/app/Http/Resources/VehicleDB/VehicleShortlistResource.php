<?php

namespace App\Http\Resources\VehicleDB;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleShortlistResource extends JsonResource
{public function toArray($request)
    {
        return [
            'id' => data_get($this, 'id'),
            'unit_number' => data_get($this, 'unit_number'),
        ];
    }
}


/**
 *  * @OA\Schema(schema="VehicleShortlistResourceRaw", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="unit_number", type="string"),
 *         )
 *     }
 * )
 *
 *  * @OA\Schema(schema="VehicleShortlistResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/VehicleShortlistResourceRaw")
 *     ),
 * )
 *
 */
