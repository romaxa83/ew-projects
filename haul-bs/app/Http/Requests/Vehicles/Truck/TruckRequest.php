<?php

namespace App\Http\Requests\Vehicles\Truck;

use App\Dto\Vehicles\TruckDto;
use App\Http\Requests\Vehicles\VehicleRequest;
use App\Models\Vehicles\Truck;
use App\Repositories\Vehicles\TruckRepository;

/**
 * @OA\Schema(type="object", title="TruckRequest",
 *     required={"vin", "unit_number", "year", "make", "model", "type", "owner_id", "license_plate"},
 *     @OA\Property(property="vin", type="string", example="1FT8W3CT3LED10823"),
 *     @OA\Property(property="unit_number", type="string", example="SL56"),
 *     @OA\Property(property="year", type="string", example="2022"),
 *     @OA\Property(property="make", type="string", example="FORD"),
 *     @OA\Property(property="model", type="string", example="F-350"),
 *     @OA\Property(property="type", type="integer", example="8"),
 *     @OA\Property(property="owner_id", type="integer", example="2"),
 *     @OA\Property(property="license_plate", type="string", example="TK348OKT"),
 *     @OA\Property(property="notes", type="string", example="some text"),
 *     @OA\Property(property="color", type="string", example="black"),
 *     @OA\Property(property="gvwr", type="number", example="100.9"),
 *     @OA\Property(property="tags", type="array", description="Tag id list", example={1, 22, 3},
 *         @OA\Items(type="integer")
 *     ),
 *     @OA\Property(property="attachment_files", type="array",
 *          @OA\Items(type="file")
 *     ),
 * )
 */

class TruckRequest extends VehicleRequest
{
    public function getDto(): TruckDto
    {
        return TruckDto::byArgs($this->validated());
    }

    public function getModel(): ?Truck
    {
        $id = $this->route('id');

        if($id !== null){
            /** @var $repo TruckRepository */
            $repo = resolve(TruckRepository::class);
            /** @var $model Truck */
            $model = $repo->getById($id);

            return $model;
        }

        return null;
    }
}
