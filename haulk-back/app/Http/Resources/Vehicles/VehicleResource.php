<?php

namespace App\Http\Resources\Vehicles;

use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vehicle
 */
class VehicleResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="Vehicle",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Vehicle data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "vin", "make", "model", "year", "vehicle_form"},
     *                     @OA\Property(property="id", type="integer", description="Vehicle id"),
     *                     @OA\Property(property="vin", type="string", description="Vehicle vin"),
     *                     @OA\Property(property="make", type="string", description="Vehicle Make"),
     *                     @OA\Property(property="model", type="string", description="Vehicle Model"),
     *                     @OA\Property(property="year", type="string", description="Vehicle Year"),
     *                     @OA\Property(property="vehicle_form", type="string", description="Vehicle form"),
     *                 )
     *             }
     *         ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vin' => $this->vin,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'vehicle_form' => $this->vehicle_form,
        ];
    }
}
