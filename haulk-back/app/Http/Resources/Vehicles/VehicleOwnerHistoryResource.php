<?php

namespace App\Http\Resources\Vehicles;

use App\Models\Vehicles\VehicleOwnerHistory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VehicleOwnerHistory
 */
class VehicleOwnerHistoryResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="VehicleOwnerHistoryOwnerResource",
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
     *    schema="VehicleOwnerHistoryRawResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Item id"),
     *            @OA\Property(property="owner", type="object", ref="#/components/schemas/VehicleOwnerHistoryOwnerResource"),
     *            @OA\Property(property="assigned_at", type="integer", description="Assigned at timestamp"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="VehicleOwnerHistoryResource",
     *    @OA\Property(
     *        property="data",
     *        description="Vehicle owners history",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/VehicleOwnerHistoryRawResource")
     *    ),
     *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'owner' => $this->owner
                ? [
                    'id' => $this->owner->id ?? null,
                    'full_name' => $this->owner->full_name ?? null,
                ]
                : null,
            'assigned_at' => $this->assigned_at->timestamp,
        ];
    }
}
