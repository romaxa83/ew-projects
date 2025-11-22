<?php

namespace App\Http\Resources\BodyShop\Orders;

use App\Models\BodyShop\Orders\TypeOfWork;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TypeOfWork
 */
class OrderTypeOfWorkResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="OrderTypeOfWork",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "name", "duration", "hourly_rate"},
     *             @OA\Property(property="id", type="integer", description="Type Of Work id"),
     *             @OA\Property(property="name", type="string", description="Type Of Work Name"),
     *             @OA\Property(property="duration", type="string", description="Type Of Work Duration"),
     *             @OA\Property(property="hourly_rate", type="number", description="Type Of Work Hourly rate"),
     *             @OA\Property(
     *                 property="inventories",
     *                 description="Type Of Work inventories data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/OrderTypeOfWorkInventory")
     *             ),
     *             @OA\Property(property="total_amount", type="numner", description="Type of Work total amount"),
     *         )
     *     }
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration,
            'hourly_rate' => $this->hourly_rate,
            'inventories' => $this->inventories ? OrderTypeOfWorkInventoryResource::collection($this->inventories): null,
            'total_amount' => round($this->getAmount(), 2),
        ];
    }
}
