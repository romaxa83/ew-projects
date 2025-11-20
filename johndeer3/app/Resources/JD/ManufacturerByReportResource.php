<?php

namespace App\Resources\JD;

use App\Models\JD\Manufacturer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Manufacturer by Report Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="name", type="string", description="Название", example="John Deere"),
 * )
 */

class ManufacturerByReportResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Manufacturer $manufacturer */
        $manufacturer = $this;

        return [
            'id' => $manufacturer->id,
            'name' => $manufacturer->name,
        ];
    }
}
