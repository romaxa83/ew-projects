<?php

namespace App\Http\Resources\Users;

use App\Models\Vehicles\VehicleOwnerHistory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VehicleOwnerHistory
 */
class OwnerVehiclesHistoryResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="OwnerVehiclesHistoryVehicleResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Vehicle id"),
     *            @OA\Property(property="vin", type="string", description="Vehicle vin"),
     *            @OA\Property(property="unit_number", type="string", description="Vehicle unit number"),
     *            @OA\Property(property="driver_name", type="string", description="Vehicle driver name"),
     *            @OA\Property(property="driver_id", type="integer", description="Vehicle driver id"),
     *        )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="OwnerVehiclesHistoryRawResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Item id"),
     *            @OA\Property(property="vehicle", type="object", ref="#/components/schemas/OwnerVehiclesHistoryVehicleResource"),
     *            @OA\Property(property="assigned_at", type="integer", description="Assigned at timestamp"),
     *            @OA\Property(property="unassigned_at", type="integer", description="Unassigned at timezone"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="OwnerVehiclesHistoryResource",
     *    @OA\Property(
     *        property="data",
     *        description="Owner vehicles history",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/OwnerVehiclesHistoryRawResource")
     *    ),
     *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vehicle' => $this->vehicle
                ? [
                    'id' => $this->vehicle->id,
                    'vin' => $this->vehicle->vin,
                    'unit_number' => $this->vehicle->unit_number,
                    'driver_id' => $this->vehicle->driver_id ?? null,
                    'driver_name' => $this->vehicle->driver ?
                        $this->vehicle->driver->first_name . ' ' . $this->vehicle->driver->last_name
                        :null,
                ]
                : null,
            'assigned_at' => $this->assigned_at->timestamp,
            'unassigned_at' => $this->unassigned_at->timestamp ?? null,
        ];
    }
}
