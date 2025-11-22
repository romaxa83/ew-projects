<?php

namespace App\Http\Resources\Saas\GPS\History;

use Illuminate\Http\Resources\Json\JsonResource;

class HistoryRouteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'location' => [
                'lat' => data_get($this, 'location.lat'),
                'lng' => data_get($this, 'location.lng')
            ],
            'speeding' => data_get($this, 'speeding'),
            'timestamp' => data_get($this, 'timestamp'),
        ];
    }
}

/**
 * @OA\Schema(schema="HistoryDeviceRouteRawResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="location", type="object",
 *                   @OA\Property(property="lat", type="float", example=49.069782),
 *                   @OA\Property(property="lng", type="float", example=28.632826),
 *              ),
 *             @OA\Property(property="speeding", type="boolean"),
 *             @OA\Property(property="timestamp", type="string"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="HistoryDeviceRouteResource", type="object",
 *     @OA\Property(property="data", type="object",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/HistoryDeviceRouteRawResource")
 *         }
 *     )
 * )
 *
 * @OA\Schema(schema="HistoryDeviceRouteArrayResource", type="object",
 *     @OA\Property(property="data", type="array",
 *         @OA\Items(type="array",
 *             @OA\Items(
 *                 @OA\Property(property="location", type="object",
 *                     @OA\Property(property="lat", type="float", example=49.069782),
 *                     @OA\Property(property="lng", type="float", example=28.632826),
 *                 ),
 *                 @OA\Property(property="speeding", type="boolean"),
 *                 @OA\Property(property="timestamp", type="string"),
 *             )
 *          )
 *      )
 *
 *  )
 */

