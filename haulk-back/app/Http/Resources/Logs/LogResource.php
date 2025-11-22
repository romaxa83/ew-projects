<?php

namespace App\Http\Resources\Logs;

use App\Models\Logs\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Log
 */
class LogResource extends JsonResource
{
    /**
     * @OA\Schema(schema="LogPaginatedResource",
     *   @OA\Property(property="data", description="Logs paginated list", type="array",
     *      @OA\Items(ref="#/components/schemas/LogResource")
     *   ),
     *   @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *   @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */

    /**
     * @OA\Schema(schema="LogResource", type="object", allOf={
     *          @OA\Schema(required={"id", "message", "level","level_name","created_at"},
     *              @OA\Property(property="id", type="integer"),
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="level", type="integer"),
     *              @OA\Property(property="level_name", type="string"),
     *              @OA\Property(property="created_at", type="string"),
     *          )
     *      }
     * )
     */

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'level' => $this->level,
            'level_name' => $this->level_name,
            'created_at' => Carbon::createFromTimestamp($this->unix_time)->format(config('formats.datetime')),
        ];
    }
}
