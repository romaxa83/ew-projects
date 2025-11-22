<?php

namespace App\Http\Resources\Vehicles\Trucks;

use App\Models\Vehicles\Truck;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Truck
 */
class TruckSimpleResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="TruckSimpleResource", type="object", allOf={
     *       @OA\Schema(
     *          required={"id", "vin", "unit_number", "year", "make", "model", "type"},
     *          @OA\Property(property="id", type="integer", description="Truck id"),
     *          @OA\Property(property="unit_number", type="string", description="Unit number"),
     *          @OA\Property(property="vin", type="string", description="Truck vin"),
     *          @OA\Property(property="make", type="string", description="Truck make"),
     *          @OA\Property(property="model", type="string", description="Truck model"),
     *          @OA\Property(property="year", type="string", description="Truck year"),
     *          @OA\Property(property="type", type="integer", description="Truck type"),
     *      )
     *  })
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'unit_number' => $this->unit_number,
            'vin' => $this->vin,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'type' => $this->type,
        ];
    }
}

