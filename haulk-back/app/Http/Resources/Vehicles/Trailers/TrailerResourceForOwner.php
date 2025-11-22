<?php

namespace App\Http\Resources\Vehicles\Trailers;

use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Users\UserShortResource;
use App\Models\Vehicles\Trailer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Trailer
 */
class TrailerResourceForOwner extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TrailerForOwner",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Trailer data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "vin", "unit_number", "year", "make", "model"},
     *                     @OA\Property(property="id", type="integer", description="Trailer id"),
     *                     @OA\Property(property="vin", type="string", description="Trailer vin"),
     *                     @OA\Property(property="unit_number", type="string", description="Trailer Unit number"),
     *                     @OA\Property(property="license_plate", type="string", description="Trailer Licence plate"),
     *                     @OA\Property(property="temporary_plate", type="string", description="Trailer Temporary plate"),
     *                     @OA\Property(property="make", type="string", description="Trailer make"),
     *                     @OA\Property(property="model", type="string", description="Trailer model"),
     *                     @OA\Property(property="year", type="string", description="Trailer year"),
     *                     @OA\Property(property="driver", ref="#/components/schemas/UserShort"),
     *                     @OA\Property(property="tags", type="array", description="Trailer tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *                     @OA\Property(property="color", type="string", description="Color"),
     *                     @OA\Property(property="gvwr", type="number", description="GVWR"),
     *                 )
     *             }
     *         ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vin' => $this->vin,
            'unit_number' => $this->unit_number,
            'license_plate' => $this->license_plate,
            'temporary_plate' => $this->temporary_plate,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'driver' => $this->driver ? UserShortResource::make($this->driver) : null,
            'tags' => TagShortResource::collection($this->tags),
            'color' => $this->color,
            'gvwr' => $this->gvwr,
        ];
    }
}
