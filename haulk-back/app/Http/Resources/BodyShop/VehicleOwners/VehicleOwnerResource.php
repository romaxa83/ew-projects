<?php

namespace App\Http\Resources\BodyShop\VehicleOwners;

use App\Http\Resources\BodyShop\Vehicles\Trailers\TrailerResourceForVehicleOwner;
use App\Http\Resources\BodyShop\Vehicles\Trucks\TruckResourceForVehicleOwner;
use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VehicleOwner
 */
class VehicleOwnerResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="VehicleOwner",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Vehicle Owner data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "fisrt_name", "last_name", "phone", "email"},
     *                     @OA\Property(property="id", type="integer", description="Vehicle Owner id"),
     *                     @OA\Property(property="first_name", type="string", description="Vehicle Owner first name"),
     *                     @OA\Property(property="last_name", type="string", description="Vehicle Owner last name"),
     *                     @OA\Property(property="email", type="string", description="Vehicle Owner email"),
     *                     @OA\Property(property="phone", type="string", description="Vehicle Owner phone"),
     *                     @OA\Property(property="phone_extension", type="string", description="User phone extension"),
     *                     @OA\Property(property="phones", type="array", description="Vehicle Owner aditional phones",
     *                         @OA\Items(ref="#/components/schemas/PhonesRaw")
     *                     ),
     *                     @OA\Property(property="notes", type="string", description="Vehicle Owner notes"),
     *                     @OA\Property(property="attachments", type="array", description="Vehicle Owner attachments", @OA\Items(ref="#/components/schemas/FileRaw")),
     *                     @OA\Property(property="tags", type="array", description="Vehicle Owner tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *                     @OA\Property(property="hasRelatedEntities", type="boolean", description="Is Vehicle Owner can be deleted"),
     *                     @OA\Property(property="trucks", type="array", description="Vehicle Owner trucks",
     *                         @OA\Items(ref="#/components/schemas/TruckBSForOwner")
     *                     ),
     *                     @OA\Property(property="trailers", type="array", description="Vehicle Owner trailers",
     *                         @OA\Items(ref="#/components/schemas/TrailerBSForOwner")
     *                     ),
     *                 )
     *             }
     *         ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones,
            'notes' => $this->notes,
            VehicleOwner::ATTACHMENT_COLLECTION_NAME => FileResource::collection($this->getAttachments()),
            'tags' => TagShortResource::collection($this->tags),
            // TODO add truck/trailer relation
            'hasRelatedEntities' => false,
            'trucks' => $this->trucks ? TruckResourceForVehicleOwner::collection($this->trucks) : null,
            'trailers' => $this->trailers ? TrailerResourceForVehicleOwner::collection($this->trailers) : null,
        ];
    }
}
