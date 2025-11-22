<?php

namespace App\Http\Resources\TypeOfWorks;

use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="TypeOfWorkResource", type="object",
 *     @OA\Property(property="data", type="object", description="Type Of Work data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "duration", "hourly_rate"},
 *             @OA\Property(property="id", type="integer", description="Type Of Work id"),
 *             @OA\Property(property="name", type="string", description="Type Of Work Name"),
 *             @OA\Property(property="duration", type="string", description="Type Of Work Duration"),
 *             @OA\Property(property="hourly_rate", type="number", description="Type Of Work Hourly rate"),
 *             @OA\Property(property="inventories", description="Type Of Work inventories data", type="array",
 *                 @OA\Items(ref="#/components/schemas/TypeOfWorkInventory")
 *             ),
 *             @OA\Property(property="estimated_amount", type="number", description="Type of Work Estimated Amount"),
 *         )
 *     }),
 * )
 *
 * @mixin TypeOfWork
 */
class TypeOfWorkResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration,
            'hourly_rate' => $this->hourly_rate,
            'inventories' => TypeOfWorkInventoryResource::collection($this->inventories),
            'estimated_amount' => round($this->getEstimatedAmount(), 2),
        ];
    }
}
