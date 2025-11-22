<?php

namespace App\Http\Resources\Vehicles;

use App\Http\Resources\Tags\TagShortResource;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="VehicleForCustomerResource", type="object",
 *     @OA\Property(property="data", type="object", description="Vehicle data", allOf={
 *         @OA\Schema(
 *             required={"id", "vin", "unit_number", "year", "make", "model", "type"},
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="vin", type="string", example="1FT8W3CT3LED10823"),
 *             @OA\Property(property="unit_number", type="string", example="SL56"),
 *             @OA\Property(property="license_plate", type="string", example="TK348OKT"),
 *             @OA\Property(property="make", type="string", example="FORD"),
 *             @OA\Property(property="model", type="string", example="F-350"),
 *             @OA\Property(property="year", type="string", example="2022"),
 *             @OA\Property(property="type", type="integer", example="8"),
 *             @OA\Property(property="tags", type="array", description="Truck tags",
 *                 @OA\Items(ref="#/components/schemas/TagRawShort")
 *             ),
 *         )}
 *     ),
 * )
 *
 * @mixin Vehicle
 */

class VehicleForCustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vin' => $this->vin,
            'unit_number' => $this->unit_number,
            'license_plate' => $this->license_plate,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'type' => $this->type,
            'tags' => TagShortResource::collection($this->tags),
        ];
    }
}
