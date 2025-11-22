<?php

namespace App\Http\Resources\Vehicles;

use App\Models\Orders\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vehicle
 */
class SameVinResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="SameVinResource",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        @OA\Items(
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer", description="Vehicle ID"),
     *                @OA\Property(property="make", type="string", description="Vehicle Make"),
     *                @OA\Property(property="model", type="string", description="Vehicle Model"),
     *                @OA\Property(property="unit_number", type="string", description="Vehicle Unit Number"),
     *            )
     *        }
     *     )
     *    ),
     * )
     *
     */
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
