<?php

namespace App\Http\Requests\Vehicles\Trailer;

use App\Dto\Vehicles\TrailerDto;
use App\Http\Requests\Vehicles\VehicleRequest;
use App\Models\Vehicles\Trailer;
use App\Repositories\Vehicles\TrailerRepository;

/**
 * @OA\Schema(type="object", title="TrailerRequest",
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

class TrailerRequest extends VehicleRequest
{
    public function rules(): array
    {
        $rule = parent::rules();
        unset($rule['type']);

        return $rule;
    }

    public function getDto(): TrailerDto
    {
        return TrailerDto::byArgs($this->validated());
    }

    public function getModel(): ?Trailer
    {
        $id = $this->route('id');

        if($id !== null){
            /** @var $repo TrailerRepository */
            $repo = resolve(TrailerRepository::class);
            /** @var $model Trailer */
            $model = $repo->getById($id);

            return $model;
        }

        return null;
    }
}
