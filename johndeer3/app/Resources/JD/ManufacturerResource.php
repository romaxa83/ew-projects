<?php

namespace App\Resources\JD;

use App\Helpers\DateFormat;
use App\Models\JD\Manufacturer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Manufacturer by Report Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="jd_id", type="integer", description="ID в системе JD", example=14),
 *     @OA\Property(property="name", type="string", description="Название", example="John Deere"),
 *     @OA\Property(property="is_parent", type="boolean", description="Являеться ли партнером JD", example=false),
 *     @OA\Property(property="created", type="string", description="Создание", example="27.04.2022 22:23"),
 *     @OA\Property(property="updated", type="string", description="Обновление", example="27.04.2022 22:23"),
 * )
 */

class ManufacturerResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Manufacturer $manufacturer */
        $manufacturer = $this;

        return [
            'id' => $manufacturer->id,
            'jd_id' => $manufacturer->jd_id,
            'name' => $manufacturer->name,
            'is_parent' => $manufacturer->isPartner(),
            'created' => DateFormat::front($manufacturer->created_at),
            'updated' => DateFormat::front($manufacturer->updated_at)
        ];
    }
}

