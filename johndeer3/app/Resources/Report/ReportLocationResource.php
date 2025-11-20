<?php

namespace App\Resources\Report;

use App\Models\Report\Location;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="ReportLocationResource",
 *     @OA\Property(property="lat", type="string", description="Широта", example="46.6372162"),
 *     @OA\Property(property="long", type="string", description="Долгота", example="32.6121012"),
 *     @OA\Property(property="country", type="string", description="Страна", example="Украина"),
 *     @OA\Property(property="city", type="string", description="Город", example="Херсон"),
 *     @OA\Property(property="region", type="string", description="Область", example="Херсонська область"),
 *     @OA\Property(property="zipcode", type="string", description="Почтовый индекс", example="73000"),
 *     @OA\Property(property="street", type="string", description="Улица", example="вулиця Ярослава Мудрого16"),
 * )
 */

class ReportLocationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Location $location */
        $location = $this;

        return [
            'lat' => $location->lat,
            'long' => $location->long,
            'country' => $location->country,
            'city' => $location->city,
            'region' => $location->region,
            'zipcode' => $location->zipcode,
            'street' => $location->street,
        ];
    }
}
