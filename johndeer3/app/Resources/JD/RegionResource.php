<?php

namespace App\Resources\JD;

use App\Models\JD\Region;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Region Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="jd_id", type="integer", description="ID в системе JD", example=14),
 *     @OA\Property(property="name", type="string", description="Название", example="Київська"),
 *     @OA\Property(property="status", type="boolean", description="Активен", example=true),
 * )
 */

class RegionResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Region $region */
        $region = $this;

        return [
            'id' => $region->id,
            'jd_id' => $region->jd_id,
            'name' => $region->name,
            'status' => $region->status
        ];
    }
}
