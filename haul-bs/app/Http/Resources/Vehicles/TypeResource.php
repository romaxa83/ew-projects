<?php

namespace App\Http\Resources\Vehicles;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="VehicleTypeRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name"},
 *         @OA\Property(property="id", type="integer", example="12"),
 *         @OA\Property(property="name", type="string", example="Sedan"),
 *     )}
 * )
 *
 * @OA\Schema(schema="VehicleTypeResource",
 *     @OA\Property(property="data", description="Vehicle type data", type="array",
 *         @OA\Items(ref="#/components/schemas/VehicleTypeRaw")
 *     ),
 * )
 *
 * @mixin array
 */

class TypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this['id'],
            'name' => $this['name'],
        ];
    }
}
