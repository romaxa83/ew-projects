<?php

namespace App\Http\Resources\Orders;

use App\Models\Orders\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vehicle
 */
class VehicleResource extends JsonResource
{
    /**
     *
     * @OA\Schema(
     *    schema="VehicleResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="inop", type="boolean", description="Vehicle inop"),
     *                @OA\Property(property="enclosed", type="boolean", description="Vehicle enclosed"),
     *                @OA\Property(property="vin", type="string", description="Vehicle vin"),
     *                @OA\Property(property="year", type="string", description="Vehicle year"),
     *                @OA\Property(property="make", type="string", description="Vehicle make"),
     *                @OA\Property(property="model", type="string", description="Vehicle model"),
     *                @OA\Property(property="type_id", type="number", description="Vehicle type"),
     *                @OA\Property(property="color", type="string", description="Vehicle color"),
     *                @OA\Property(property="license_plate", type="string", description="Vehicle license plate"),
     *                @OA\Property(property="odometer", type="number", description="Vehicle odometer value"),
     *                @OA\Property(property="stock_number", type="string", description="Vehicle stock number"),
     *                @OA\Property(property="pickup_inspection", type="object", description="Vehicle inspection", allOf={@OA\Schema(ref="#/components/schemas/InspectionResource")}),
     *                @OA\Property(property="delivery_inspection", type="object", description="Vehicle inspection", allOf={@OA\Schema(ref="#/components/schemas/InspectionResource")}),
     *            )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="VehicleResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Vehicle data",
     *            allOf={
     *                @OA\Schema(
     *                    @OA\Property(property="inop", type="boolean", description="Vehicle inop"),
     *                    @OA\Property(property="enclosed", type="boolean", description="Vehicle enclosed"),
     *                    @OA\Property(property="vin", type="string", description="Vehicle vin"),
     *                    @OA\Property(property="year", type="string", description="Vehicle year"),
     *                    @OA\Property(property="make", type="string", description="Vehicle make"),
     *                    @OA\Property(property="model", type="string", description="Vehicle model"),
     *                    @OA\Property(property="type_id", type="number", description="Vehicle type"),
     *                    @OA\Property(property="color", type="string", description="Vehicle color"),
     *                    @OA\Property(property="license_plate", type="string", description="Vehicle license plate"),
     *                    @OA\Property(property="odometer", type="number", description="Vehicle odometer value"),
     *                    @OA\Property(property="stock_number", type="string", description="Vehicle stock number"),
     *                    @OA\Property(property="pickup_inspection", type="object", description="Vehicle inspection", allOf={@OA\Schema(ref="#/components/schemas/InspectionResource")}),
     *                    @OA\Property(property="delivery_inspection", type="object", description="Vehicle inspection", allOf={@OA\Schema(ref="#/components/schemas/InspectionResource")}),
     *                )
     *            }
     *        ),
     * )
     *
     */

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'inop' => (bool)$this->inop,
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
            'pickup_inspection' => new InspectionResource($this->pickupInspection),
            'delivery_inspection' => new InspectionResource($this->deliveryInspection),
        ];
    }
}
