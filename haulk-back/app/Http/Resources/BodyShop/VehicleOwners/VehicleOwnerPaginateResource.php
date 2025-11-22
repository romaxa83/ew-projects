<?php

namespace App\Http\Resources\BodyShop\VehicleOwners;

use App\Http\Resources\Tags\TagShortResource;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VehicleOwner
 */
class VehicleOwnerPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="VehicleOwnerRaw",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Vehicle Owner data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "first_name", "last_name", "phone", "email"},
     *                     @OA\Property(property="id", type="integer", description="Vehicle Owner id"),
     *                     @OA\Property(property="first_name", type="string", description="Vehicle Owner first_name"),
     *                     @OA\Property(property="last_name", type="string", description="Vehicle Owner last_name"),
     *                     @OA\Property(property="email", type="string", description="Vehicle Owner email"),
     *                     @OA\Property(property="phone", type="string", description="Vehicle Owner phone"),
     *                     @OA\Property(property="phone_extension", type="string", description="Vehicle Owner phone extension"),
     *                     @OA\Property(property="tags", type="array", description="Vehicle Owner tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *                     @OA\Property(property="hasRelatedTrucks", type="boolean", description="Is Vehicle Owner has related trucks"),
     *                     @OA\Property(property="hasRelatedTrailers", type="boolean", description="Is Vehicle Owner has related trailers"),
     *                     @OA\Property(property="comments_count", type="integer", description="Comments count"),
     *                 )
     *             }
     *         ),
     * )
     *
     * @OA\Schema(
     *     schema="VehicleOwnerPaginate",
     *     @OA\Property(
     *         property="data",
     *         description="Vehicle Owner paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/VehicleOwnerRaw")
     *     ),
     *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
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
            'tags' => TagShortResource::collection($this->tags),
            'hasRelatedTrucks' => $this->trucks()->exists(),
            'hasRelatedTrailers' => $this->trailers()->exists(),
            'comments_count' => $this->comments()->count(),
        ];
    }
}
