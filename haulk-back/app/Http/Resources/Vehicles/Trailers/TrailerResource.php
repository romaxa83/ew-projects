<?php

namespace App\Http\Resources\Vehicles\Trailers;

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
     *     schema="Trailer",
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
     *                     @OA\Property(property="owner", ref="#/components/schemas/UserShort"),
     *                     @OA\Property(property="driver", ref="#/components/schemas/UserShort"),
     *                     @OA\Property(property="driver_attach_at", type="integer", description="Attach driver date, timestamp"),
     *                     @OA\Property(property="tags", type="array", description="Trailer tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *                     @OA\Property(property="notes", type="string", description="Trailer notes"),
     *                     @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Is trailer has related open orders"),
     *                     @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Is trailer has related deleted orders"),
     *                     @OA\Property(property="attachments", type="array", description="Vehicle attachments", @OA\Items(ref="#/components/schemas/FileRaw")),
     *                     @OA\Property(property="color", type="string", description="Color"),
     *                     @OA\Property(property="gvwr", type="number", description="GVWR"),
     *                     @OA\Property(property="registration_number", type="string", description="Registration number"),
     *                     @OA\Property(property="registration_date", type="string", description="Registration date, format=m/d/Y"),
     *                     @OA\Property(property="registration_expiration_date", type="string", description="Registration expiration date, format=m/d/Y"),
     *                     @OA\Property(property="registration_file", type="object", description="Registration file", allOf={
     *                         @OA\Schema(ref="#/components/schemas/FileRaw")
     *                     }),
     *                     @OA\Property(property="isRegistrationDocumentExpires", type="boolean", description="Is registration document expires"),
     *                     @OA\Property(property="inspection_date", type="string", description="Inspection date, format=m/d/Y"),
     *                     @OA\Property(property="inspection_expiration_date", type="string", description="Inspection expiration date, format=m/d/Y"),
     *                     @OA\Property(property="inspection_file", type="object", description="Inspection file", allOf={
     *                         @OA\Schema(ref="#/components/schemas/FileRaw")
     *                     }),
     *                     @OA\Property(property="isInspectionDocumentExpires", type="boolean", description="Is inspection document expires"),
     *                     @OA\Property(property="gpsDevice", type="object", description="Gps Device (if Gps Enabled)", allOf={
     *                         @OA\Schema(ref="#/components/schemas/GPSDeviceCRMRawResource")
     *                     }),
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
            'owner' => $this->owner ? UserShortResource::make($this->owner) : null,
            'driver' => $this->driver ? UserShortResource::make($this->driver) : null,
            'driver_attach_at' => $this->driver_attach_at->timestamp ?? null,
            'tags' => TagShortResource::collection($this->tags),
            'notes' => $this->notes,
            'hasRelatedOpenOrders' => $this->hasRelatedOpenOrders(),
            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedOrders(),
            Vehicle::ATTACHMENT_COLLECTION_NAME => FileResource::collection($this->getAttachments()),
            'color' => $this->color,
            'gvwr' => $this->gvwr,
            'inspection_date' => $this->getInspectionDate(),
            'inspection_expiration_date' => $this->getInspectionExpirationDate(),
            Vehicle::INSPECTION_DOCUMENT_NAME => $this->getFile(Vehicle::INSPECTION_DOCUMENT_NAME)
                ? FileResource::make($this->getFile(Vehicle::INSPECTION_DOCUMENT_NAME))
                : null,
            'isInspectionDocumentExpires' => $this->isInspectionDocumentExpires(),
            'registration_number' => $this->registration_number,
            'registration_date' => $this->getRegistrationDate(),
            'registration_expiration_date' => $this->getRegistrationExpirationDate(),
            Vehicle::REGISTRATION_DOCUMENT_NAME => $this->getFile(Vehicle::REGISTRATION_DOCUMENT_NAME)
                ? FileResource::make($this->getFile(Vehicle::REGISTRATION_DOCUMENT_NAME))
                : null,
            'isRegistrationDocumentExpires' => $this->isRegistrationDocumentExpires(),
            'gpsDevice' => $this->gpsDevice
                ? [
                    'id' => $this->gpsDevice->id,
                    'imei' => $this->gpsDevice->imei,
                ]
                : null,
        ];
    }
}
