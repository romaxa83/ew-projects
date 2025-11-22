<?php

namespace App\Http\Resources\VehicleDB;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleModelResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="VehicleModelSingle",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            required={"id", "make_id", "name"},
     *            @OA\Property(property="id", type="integer", description="Vehicle model id"),
     *            @OA\Property(property="name", type="string", description="Vehicle model name"),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="VehicleModelResource",
     *    @OA\Property(
     *        property="data",
     *        description="vehicle model data",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/VehicleModelSingle")
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
