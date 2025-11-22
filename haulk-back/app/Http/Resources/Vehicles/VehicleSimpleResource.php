<?php

namespace App\Http\Resources\Vehicles;

use App\Http\Resources\Users\UserShortResource;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vehicle
 */
class VehicleSimpleResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="VehicleSimpleResource",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Vehicle data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "vin", "make", "model", "year", "vehicle_form"},
     *                     @OA\Property(property="id", type="integer", description="Vehicle id"),
     *                     @OA\Property(property="unit_number", type="string", description="unit number"),
     *                     @OA\Property(property="vin", type="string", description="Vehicle vin"),
     *                     @OA\Property(property="make", type="string", description="Vehicle Make"),
     *                     @OA\Property(property="model", type="string", description="Vehicle Model"),
     *                     @OA\Property(property="year", type="string", description="Vehicle Year"),
     *                     @OA\Property(property="is_truck", type="boolean", description="Is truck"),
     *                     @OA\Property(property="license_plate", type="string", description="Vehicle Licence plate"),
     *                     @OA\Property(property="owner", ref="#/components/schemas/UserShort"),
     *                 )
     *             }
     *         ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'unit_number' => $this->vin,
            'vin' => $this->vin,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'is_truck' => $this->resource instanceof Truck,
            'license_plate' => $this->license_plate,
            'owner' => $this->owner ? UserShortResource::make($this->owner) : null,
        ];
    }
}

