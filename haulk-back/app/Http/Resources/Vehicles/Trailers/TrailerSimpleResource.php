<?php

namespace App\Http\Resources\Vehicles\Trailers;

use App\Models\Vehicles\Trailer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Trailer
 */
class TrailerSimpleResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="TrailerSimpleResource", type="object", allOf={
     *        @OA\Schema(
     *           required={"id", "vin", "unit_number", "year", "make", "model", "type"},
     *           @OA\Property(property="id", type="integer", description="Trailer id"),
     *           @OA\Property(property="unit_number", type="string", description="Unit number"),
     *           @OA\Property(property="vin", type="string", description="Trailer vin"),
     *           @OA\Property(property="make", type="string", description="Trailer make"),
     *           @OA\Property(property="model", type="string", description="Trailer model"),
     *           @OA\Property(property="year", type="string", description="Trailer year"),
     *       )
     *   })
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'unit_number' => $this->unit_number,
            'vin' => $this->vin,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
        ];
    }
}

