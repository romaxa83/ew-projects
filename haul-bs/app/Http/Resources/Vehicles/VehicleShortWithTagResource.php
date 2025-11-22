<?php

namespace App\Http\Resources\Vehicles;

use App\Http\Resources\Tags\TagShortResource;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="VehicleShortWithTagResource", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "vin", "make", "model", "year", "vehicle_form"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="vin", type="string", example="1FT8W3CT3LED10823"),
 *         @OA\Property(property="unit_number", type="string", example="CT3LED"),
 *         @OA\Property(property="make", type="string", example="FORD"),
 *         @OA\Property(property="model", type="string", example="F-350"),
 *         @OA\Property(property="year", type="string", example="2022"),
 *         @OA\Property(property="license_plate", type="string", example="TK348OKT"),
 *         @OA\Property(property="temporary_plate", type="string", example="651133T"),
 *         @OA\Property(property="vehicle_form", type="string", description="Vehicle form", example="truck", enum={"trucks", "trailer"}),
 *         @OA\Property(property="tags", type="array", description="Truck tags",
 *             @OA\Items(ref="#/components/schemas/TagRawShort")
 *         ),
 *     )}
 * )
 *
 * @mixin Vehicle|Truck|Trailer
 */

class VehicleShortWithTagResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vin' => $this->vin,
            'unit_number' => $this->unit_number,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'license_plate' => $this->license_plate,
            'temporary_plate' => $this->temporary_plate,
            'vehicle_form' => $this->getMorphName(),
            'tags' => TagShortResource::collection($this->tags),
        ];
    }
}

