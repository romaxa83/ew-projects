<?php

namespace App\Http\Resources\VehicleDB;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleMakeResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="VehicleMakeSingle",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            required={"id", "name"},
     *            @OA\Property(property="id", type="integer", description="Vehicle make id"),
     *            @OA\Property(property="name", type="string", description="Vehicle make name"),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="VehicleMakeResource",
     *    @OA\Property(
     *        property="data",
     *        description="vehicle make data",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/VehicleMakeSingle")
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
