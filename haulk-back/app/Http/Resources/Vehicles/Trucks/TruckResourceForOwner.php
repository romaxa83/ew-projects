<?php

namespace App\Http\Resources\Vehicles\Trucks;

use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Users\UserShortResource;
use App\Models\Vehicles\Truck;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Truck
 */
class TruckResourceForOwner extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TruckForOwner",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Truck data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "vin", "unit_number", "year", "make", "model", "type"},
     *                     @OA\Property(property="id", type="integer", description="Truck id"),
     *                     @OA\Property(property="vin", type="string", description="Truck vin"),
     *                     @OA\Property(property="unit_number", type="string", description="Truck Unit number"),
     *                     @OA\Property(property="license_plate", type="string", description="Truck Licence plate"),
     *                     @OA\Property(property="temporary_plate", type="string", description="Truck Temporary plate"),
     *                     @OA\Property(property="make", type="string", description="Truck make"),
     *                     @OA\Property(property="model", type="string", description="Truck model"),
     *                     @OA\Property(property="year", type="string", description="Truck year"),
     *                     @OA\Property(property="type", type="integer", description="Truck type"),
     *                     @OA\Property(property="driver", ref="#/components/schemas/UserShort"),
     *                     @OA\Property(property="tags", type="array", description="Truck tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *                     @OA\Property(property="color", type="string", description="Color"),
     *                     @OA\Property(property="gvwr", type="number", description="GVWR"),
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
            'unit_number' => $this->unit_number,
            'license_plate' => $this->license_plate,
            'temporary_plate' => $this->temporary_plate,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'type' => $this->type,
            'driver' => $this->driver ? UserShortResource::make($this->driver) : null,
            'tags' => TagShortResource::collection($this->tags),
            'color' => $this->color,
            'gvwr' => $this->gvwr,
        ];
    }
}
