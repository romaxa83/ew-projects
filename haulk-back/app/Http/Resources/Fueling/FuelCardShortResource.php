<?php

namespace App\Http\Resources\Fueling;

use App\Models\Fueling\FuelCard;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FuelCard
 */
class FuelCardShortResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="FuelCardShortResource",
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
     *            @OA\Property(property="created_at", type="integer", description="timestamp"),
     *            @OA\Property(property="updated_at", type="integer", description="timestamp"),
     *            @OA\Property(property="deactivated_at", type="integer", description="timestamp"),
     *        )
     *    }
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'card' => $this->card,
            'active' => $this->active,
            'provider' => $this->provider,
            'status' => $this->status,
            'created_at' => $this->created_at->timestamp ?? null,
            'updated_at' => $this->updated_at->timestamp ?? null,
            'deactivated_at' => $this->deactivated_at->timestamp ?? null,
        ];
    }
}
