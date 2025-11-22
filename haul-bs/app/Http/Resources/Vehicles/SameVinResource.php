<?php

namespace App\Http\Resources\Vehicles;

use App\Models\Vehicles\Truck;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="SameVinResource",
 *     @OA\Property(property="data", type="array",
 *         @OA\Items(allOf={
 *             @OA\Schema(
 *                 @OA\Property(property="id", type="integer", description="Vehicle ID"),
 *                 @OA\Property(property="make", type="string", description="Vehicle Make"),
 *                 @OA\Property(property="model", type="string", description="Vehicle Model"),
 *                 @OA\Property(property="unit_number", type="string", description="Vehicle Unit Number"),
 *            )}
 *         )
 *     ),
 * )
 *
 * @mixin Truck
 */
class SameVinResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'make' => $this->make,
            'model' => $this->model,
            'unit_number' => $this->unit_number,
        ];
    }
}
