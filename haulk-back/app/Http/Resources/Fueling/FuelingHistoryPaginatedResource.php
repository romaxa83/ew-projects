<?php

namespace App\Http\Resources\Fueling;

use App\Http\Resources\Users\UserMiniResource;
use App\Models\Fueling\FuelingHistory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FuelingHistory
 */
class FuelingHistoryPaginatedResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="FuelingHistoryResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Record id"),
     *            @OA\Property(property="total", type="integer", description="total"),
     *            @OA\Property(property="count_errors", type="integer", description="count_errors"),
     *            @OA\Property(property="counts_success", type="integer", description="counts_success"),
     *            @OA\Property(property="progress", type="integer", description="progress"),
     *            @OA\Property(property="path_file", type="string", description="path_file"),
     *            @OA\Property(property="original_name", type="string", description="original_name"),
     *            @OA\Property(property="started_at", type="string", description="started_at"),
     *            @OA\Property(property="ended_at", type="string", description="ended_at"),
     *            @OA\Property(
     *                 property="provider",
     *                 type="string",
     *                 description="provider (quikq|efs)",
     *                 enum={"quikq", "efs"}
     *             ),
     *            @OA\Property(
     *                property="status",
     *                type="string",
     *                description="status (success|in_progress|in_queue|completed_in_errors)",
     *                enum={"success", "in_progress", "in_queue", "completed_in_errors"}
     *            ),
     *            @OA\Property(property="created_at", type="integer", description="timestamp"),
     *            @OA\Property(property="updated_at", type="integer", description="timestamp"),
     *        )
     *    }
     * )
     * @OA\Schema(
     *     schema="FuelingHistoryPaginatedResource",
     *     @OA\Property(
     *         property="data",
     *         description="FuelCard detailed paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/FuelingHistoryResource")
     *     ),
     *     @OA\Property(
     *         property="links",
     *         ref="#/components/schemas/PaginationLinks",
     *     ),
     *     @OA\Property(
     *         property="meta",
     *         ref="#/components/schemas/PaginationMeta",
     *     ),
     *  )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'total' => $this->total,
            'count_errors' => $this->count_errors,
            'counts_success' => $this->counts_success,
            'progress' => $this->getProgress(),
            'path_file' => $this->path_file,
            'original_name' => $this->original_name,
            'status' => $this->status,
            'provider' => $this->provider,
            'created_at' => $this->created_at->timestamp ?? null,
            'updated_at' => $this->updated_at->timestamp ?? null,
            'started_at' => $this->started_at->timestamp ?? null,
            'ended_at' => $this->ended_at->timestamp ?? null,
        ];
    }
}
