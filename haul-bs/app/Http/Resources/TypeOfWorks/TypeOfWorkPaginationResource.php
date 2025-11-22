<?php

namespace App\Http\Resources\TypeOfWorks;

use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="TypeOfWorkRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Type of Work data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "hourly_rate", "duration"},
 *             @OA\Property(property="id", type="integer", description="Type of Work id"),
 *             @OA\Property(property="name", type="string", description="Type of Work Name"),
 *             @OA\Property(property="duration", type="string", description="Type of Work Duration"),
 *             @OA\Property(property="hourly_rate", type="number", description="Type of Work Hourly Rate"),
 *             @OA\Property(property="estimated_amount", type="number", description="Type of Work Estimated Amount"),
 *         )
 *     }),
 * )
 *
 * @OA\Schema(schema="TypeOfWorkPagination",
 *     @OA\Property(property="data", description="Type Of Work paginated list", type="array",
 *         @OA\Items(ref="#/components/schemas/TypeOfWorkRaw")
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 * )
 *
 * @mixin TypeOfWork
 */
class TypeOfWorkPaginationResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration,
            'hourly_rate' => $this->hourly_rate,
            'estimated_amount' => $this->getEstimatedAmount(),
        ];
    }
}
