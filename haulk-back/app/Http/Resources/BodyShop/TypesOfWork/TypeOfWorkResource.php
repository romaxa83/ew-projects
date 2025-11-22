<?php

namespace App\Http\Resources\BodyShop\TypesOfWork;

use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TypeOfWork
 */
class TypeOfWorkResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TypeOfWork",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Type Of Work data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "name", "duration", "hourly_rate"},
     *                     @OA\Property(property="id", type="integer", description="Type Of Work id"),
     *                     @OA\Property(property="name", type="string", description="Type Of Work Name"),
     *                     @OA\Property(property="duration", type="string", description="Type Of Work Duration"),
     *                     @OA\Property(property="hourly_rate", type="number", description="Type Of Work Hourly rate"),
     *                     @OA\Property(
     *                         property="inventories",
     *                         description="Type Of Work inventories data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/TypeOfWorkInventory")
     *                     ),
     *                     @OA\Property(property="estimated_amount", type="number", description="Type of Work Estimated Amount"),
     *                 )
     *             }
     *         ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration,
            'hourly_rate' => $this->hourly_rate,
            'inventories' => $this->inventories ? TypeOfWorkInventoryResource::collection($this->inventories): null,
            'estimated_amount' => round($this->getEstimatedAmount(), 2),
        ];
    }
}
