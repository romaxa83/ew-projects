<?php

namespace App\Http\Resources\Orders;

use App\Models\Orders\Vehicle;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vehicle
 */
class VehicleListResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @OA\Schema(
     *    schema="VehicleListResource",
     *    @OA\Property(
     *        property="data",
     *        description="Vehicles list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/VehicleResourceRaw")
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'inop' => (bool) $this->inop,
            'enclosed' => (bool)$this->enclosed,
            'vin' => $this->vin,
            'year' => $this->year,
            'make' => $this->make,
            'model' => $this->model,
            'type_id' => $this->type_id,
            'color' => $this->color,
            'license_plate' => $this->license_plate,
            'odometer' => $this->odometer,
            'stock_number' => $this->stock_number,
            'pickup_inspection' => InspectionResource::make($this->whenLoaded('pickupInspection')),
            'delivery_inspection' => InspectionResource::make($this->whenLoaded('deliveryInspection')),
        ];
    }
}
