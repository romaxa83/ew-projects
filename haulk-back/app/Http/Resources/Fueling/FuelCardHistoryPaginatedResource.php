<?php

namespace App\Http\Resources\Fueling;

use App\Http\Resources\Users\DriverShortForCardResource;
use App\Models\Fueling\FuelCardHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FuelCardHistory
 */
class FuelCardHistoryPaginatedResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="FuelCardHistoryResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Record id"),
     *            @OA\Property(property="active", type="boolean", description="active"),
     *            @OA\Property(property="date_assigned", type="integer", description="date_assigned"),
     *            @OA\Property(property="date_unassigned", type="integer", description="date_unassigned"),
     *            @OA\Property(property="driver", type="object", description="driver", allOf={
     *              @OA\Schema(ref="#/components/schemas/DriverShortForCard")
     *            }),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="FuelCardHistoryPaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        description="FuelCard detailed paginated list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/FuelCardHistoryResource")
     *    ),
     *    @OA\Property(
     *        property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property(
     *        property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'active' => $this->active,
            'date_assigned' => $this->date_assigned->timestamp ?? null,
            'date_unassigned' => $this->date_unassigned->timestamp ?? null,
            'driver' => DriverShortForCardResource::make($this->user),
        ];
    }
}
