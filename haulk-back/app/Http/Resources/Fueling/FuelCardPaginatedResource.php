<?php

namespace App\Http\Resources\Fueling;

use App\Http\Resources\Users\UserHistoryResource;
use App\Http\Resources\Users\UserMiniResource;
use App\Models\Fueling\FuelCard;
use App\Models\History\History;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FuelCard
 */
class FuelCardPaginatedResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="FuelCardResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Record id"),
     *            @OA\Property(property="card", type="integer", description="card number"),
     *            @OA\Property(
     *              property="provider",
     *              type="string",
     *              description="provider (efs|quikq)",
     *              enum={"efs", "quikq"}
     *            ),
     *            @OA\Property(
     *               property="status",
     *               type="string",
     *               description="status (active|inactive|deleted)",
     *               enum={"active", "inactive", "deleted"}
     *            ),
     *            @OA\Property(property="active", type="boolean", description="active"),
     *            @OA\Property(property="driver", type="object", description="driver", allOf={
     *              @OA\Schema(ref="#/components/schemas/UserMini")
     *            }),
     *            @OA\Property(property="activeHistory", type="object", description="activeHistory", allOf={
     *               @OA\Schema(ref="#/components/schemas/FuelCardHistoryResource")
     *            }),
     *            @OA\Property(property="created_at", type="integer", description="timestamp"),
     *            @OA\Property(property="updated_at", type="integer", description="timestamp"),
     *            @OA\Property(property="deleted_at", type="integer", description="timestamp"),
     *            @OA\Property(property="deactivated_at", type="integer", description="timestamp"),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="FuelCardPaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        description="FuelCard detailed paginated list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/FuelCardResource")
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
            'card' => $this->card,
            'active' => $this->active,
            'provider' => $this->provider,
            'status' => $this->status,
            'driver' => $this->driver ? UserMiniResource::make($this->driver) : null,
            'activeHistory' => $this->activeHistory ? FuelCardHistoryPaginatedResource::make($this->activeHistory) : null,
            'created_at' => $this->created_at->timestamp ?? null,
            'updated_at' => $this->updated_at->timestamp ?? null,
            'deleted_at' => $this->deleted_at->timestamp ?? null,
            'deactivated_at' => $this->deactivated_at->timestamp ?? null,
        ];
    }
}
