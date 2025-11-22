<?php

namespace App\Http\Resources\VehicleDB;

use App\Models\Orders\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DecodeVinResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="DecodeVinResource",
     *    @OA\Property(
     *        property="data",
     *        description="decode vin data",
     *        type="object",
     *        allOf={
     *            @OA\Schema(
     *                type="object",
     *                @OA\Property(property="make", type="string", description="Vehicle make"),
     *                @OA\Property(property="model", type="string", description="Vehicle model"),
     *                @OA\Property(property="year", type="string", description="Vehicle year"),
     *                @OA\Property(property="type_id", type="integer", description="Vehicle type"),
     *                @OA\Property(property="type", type="object", description="Vehicle type", allOf={@OA\Schema(
     *                    @OA\Property(property="id", type="integer", description="Vehicle type id"),
     *                    @OA\Property(property="title", type="string", description="Vehicle type title"),
     *                )}),
     *            )
     *        }
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'make' => $this['make'],
            'model' => $this['model'],
            'year' => $this['year'],
            'type_id' => isset($this['type_id']) ? $this['type_id'] : null,
            'type' => isset($this['type_id']) ? [
                'id' => $this['type_id'],
                'title' => Vehicle::VEHICLE_TYPES[$this['type_id']],
            ] : null,
        ];
    }
}
