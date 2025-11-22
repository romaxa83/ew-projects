<?php

namespace App\Http\Resources\Vehicles;

use App\Models\Vehicles\Model;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="VehicleModelRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name"},
 *         @OA\Property(property="id", type="integer", example="12"),
 *         @OA\Property(property="name", type="string", example="S Series"),
 *     )}
 * )
 *
 * @OA\Schema(schema="VehicleModelResource",
 *     @OA\Property(property="data", description="vehicle model data", type="array",
 *         @OA\Items(ref="#/components/schemas/VehicleModelRaw")
 *     ),
 * )
 *
 * @mixin Model
 */

class ModelResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
