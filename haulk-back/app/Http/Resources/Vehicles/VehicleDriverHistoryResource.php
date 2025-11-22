<?php

namespace App\Http\Resources\Vehicles;

use App\Models\Vehicles\VehicleDriverHistory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VehicleDriverHistory
 */
class VehicleDriverHistoryResource extends JsonResource
{
    /**
     *
     * @OA\Schema(
     *    schema="VehicleDriverHistoryDriverResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Driver id"),
     *            @OA\Property(property="full_name", type="string", description="Driver name"),
     *        )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="VehicleDriverHistoryRawResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Item id"),
     *            @OA\Property(property="driver", type="object", ref="#/components/schemas/VehicleDriverHistoryDriverResource"),
     *            @OA\Property(property="assigned_at", type="integer", description="Assigned at timestamp"),
     *            @OA\Property(property="unassigned_at", type="integer", description="Unassigned at timezone"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="VehicleDriverHistoryResource",
     *    @OA\Property(
     *        property="data",
     *        description="Vehicle drivers history",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/VehicleDriverHistoryRawResource")
     *    ),
     *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'driver' => $this->driver
                ? [
                    'id' => $this->driver->id ?? null,
                    'full_name' => $this->driver->full_name ?? null,
                ]
                : null,
            'assigned_at' => $this->assigned_at->timestamp,
            'unassigned_at' => $this->unassigned_at->timestamp ?? null,
        ];
    }
}
