<?php

namespace App\Http\Resources\BodyShop\Vehicles\Trucks;

use App\Http\Resources\Tags\TagShortResource;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Truck
 */
class TruckShortResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TruckShortBS",
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
     *                     @OA\Property(property="type", type="string", description="Truck type"),
     *                     @OA\Property(property="vehicle_form", type="string", description="Vehicle form"),
     *                     @OA\Property(property="tags", type="array", description="Trailer tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
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
            'tags' => $this->getCompanyId() ? null : TagShortResource::collection($this->tags),
            'vehicle_form' => Vehicle::VEHICLE_FORM_TRUCK,
        ];
    }
}
