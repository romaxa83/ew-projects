<?php

namespace App\Http\Resources\BodyShop\Vehicles\Trailers;

use App\Http\Resources\BodyShop\VehicleOwners\VehicleOwnerShortResource;
use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Users\UserShortResource;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Trailer
 */
class TrailerResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TrailerBS",
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
     *                     @OA\Property(property="owner", ref="#/components/schemas/VehicleOwnerShort"),
     *                     @OA\Property(property="driver", ref="#/components/schemas/UserShort"),
     *                     @OA\Property(property="tags", type="array", description="Trailer tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *                     @OA\Property(property="notes", type="string", description="Trailer notes"),
     *                     @OA\Property(property="company_name", type="string", description="Trailer Company name"),
     *                     @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Is trailer has related open orders"),
     *                     @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Is trailer has related deleted orders"),
     *                     @OA\Property(property="attachments", type="array", description="Vehicle attachments", @OA\Items(ref="#/components/schemas/FileRaw")),
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
            'owner' => $this->owner
                ? UserShortResource::make($this->owner)
                : $this->customer ?? VehicleOwnerShortResource::make($this->customer),
            'driver' => $this->driver ? UserShortResource::make($this->driver) : null,
            'tags' => $this->getCompanyId() ? null : TagShortResource::collection($this->tags),
            'notes' => $this->notes,
            'company_name' => $this->getCompany() ? $this->getCompany()->name : null,
            'hasRelatedOpenOrders' => $this->hasRelatedOpenOrders(),
            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedOrders(),
            Vehicle::ATTACHMENT_COLLECTION_NAME => FileResource::collection($this->getAttachments()),
            'color' => $this->color,
            'gvwr' => $this->gvwr,
        ];
    }
}
