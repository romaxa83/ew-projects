<?php

namespace App\Http\Resources\Vehicles;

use App\Models\Vehicles\Make;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="VehicleMakeRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name"},
 *         @OA\Property(property="id", type="integer", example="12"),
 *         @OA\Property(property="name", type="string", example="BENTLEY"),
 *     )}
 * )
 *
 * @OA\Schema(schema="VehicleMakeResource",
 *     @OA\Property(property="data", description="vehicle make data", type="array",
 *         @OA\Items(ref="#/components/schemas/VehicleMakeRaw")
 *     ),
 * )
 *
 * @mixin Make
 */

class MakeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
