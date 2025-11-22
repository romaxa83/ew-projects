<?php

namespace App\Http\Resources\Vehicles;

use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="VehicleShortRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "vin", "make", "model", "year", "vehicle_form"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="vin", type="string", example="1FT8W3CT3LED10823"),
 *         @OA\Property(property="make", type="string", example="FORD"),
 *         @OA\Property(property="model", type="string", example="F-350"),
 *         @OA\Property(property="year", type="string", example="2022"),
 *         @OA\Property(property="unit_number", type="string", example="2022RRT"),
 *         @OA\Property(property="vehicle_form", type="string", description="Vehicle form", example="truck", enum={"trucks", "trailer"}),
 *     )}
 * )
 *
 * @mixin Vehicle|Truck|Trailer
 */

class VehicleShortResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vin' => $this->vin,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'unit_number' => $this->unit_number,
            'vehicle_form' => $this->getMorphName(),
        ];
    }
}
