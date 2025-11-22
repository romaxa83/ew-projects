<?php

namespace App\Http\Resources\Users;

use App\Models\Vehicles\VehicleDriverHistory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VehicleDriverHistory
 */
class DriverVehiclesHistoryResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="DriverVehiclesHistoryVehicleResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Vehicle id"),
     *            @OA\Property(property="vin", type="string", description="Vehicle vin"),
     *            @OA\Property(property="unit_number", type="string", description="Vehicle unit number"),
     *            @OA\Property(property="owner_name", type="string", description="Vehicle owner name"),
     *            @OA\Property(property="owner_id", type="integer", description="Vehicle owner id"),
     *        )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="DriverVehiclesHistoryRawResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Item id"),
     *            @OA\Property(property="vehicle", type="object", ref="#/components/schemas/DriverVehiclesHistoryVehicleResource"),
     *            @OA\Property(property="assigned_at", type="integer", description="Assigned at timestamp"),
     *            @OA\Property(property="unassigned_at", type="integer", description="Unassigned at timezone"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="DriverVehiclesHistoryResource",
     *    @OA\Property(
     *        property="data",
     *        description="Driver vehicles history",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/DriverVehiclesHistoryRawResource")
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
                    'owner_id' => $this->vehicle->owner_id,
                    'owner_name' => $this->vehicle->owner ?
                        $this->vehicle->owner->first_name . ' ' . $this->vehicle->owner->last_name
                        :null,
                ]
                : null,
            'assigned_at' => $this->assigned_at->timestamp,
            'unassigned_at' => $this->unassigned_at->timestamp ?? null,
        ];
    }
}
