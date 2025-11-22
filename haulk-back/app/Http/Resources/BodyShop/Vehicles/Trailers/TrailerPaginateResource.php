<?php

namespace App\Http\Resources\BodyShop\Vehicles\Trailers;

use App\Http\Resources\Tags\TagShortResource;
use App\Models\Vehicles\Trailer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Trailer
 */
class TrailerPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TrailerRawBS",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "vin", "unit_number", "year", "make", "model"},
     *             @OA\Property(property="id", type="integer", description="Trailer id"),
     *             @OA\Property(property="vin", type="string", description="Trailer vin"),
     *             @OA\Property(property="unit_number", type="string", description="Trailer Unit number"),
     *             @OA\Property(property="license_plate", type="string", description="Trailer Licence plate"),
     *             @OA\Property(property="temporary_plate", type="string", description="Trailer Temporary plate"),
     *             @OA\Property(property="make", type="string", description="Trailer make"),
     *             @OA\Property(property="model", type="string", description="Trailer model"),
     *             @OA\Property(property="year", type="string", description="Trailer year"),
     *             @OA\Property(property="owner_name", type="string", description="Trailer owner name"),
     *             @OA\Property(property="customer_id", type="integer", description="Trailer customer id"),
     *             @OA\Property(property="driver_name", type="string", description="Trailer driver name"),
     *             @OA\Property(property="tags", type="array", description="Trailer tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *             @OA\Property(property="company_name", type="string", description="Trailer Company name"),
     *             @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Is trailer has related open orders"),
     *             @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Is trailer has related deleted orders"),
     *             @OA\Property(property="comments_count", type="int", description="Comments count"),
     *             @OA\Property(property="color", type="string", description="Color"),
     *             @OA\Property(property="gvwr", type="number", description="GVWR"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="TrailerPaginateBS",
     *     @OA\Property(
     *         property="data",
     *         description="Trailer paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/TrailerRawBS")
     *     ),
     *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
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
            'owner_name' => $this->getOwnerFullName(),
            'customer_id' => $this->customer_id ?? null,
            'driver_name' => $this->driver->full_name ?? null,
            'tags' => $this->getCompanyId() ? null : TagShortResource::collection($this->tags),
            'company_name' => $this->getCompany() ? $this->getCompany()->name : null,
            'hasRelatedOpenOrders' => $this->hasRelatedOpenOrders(),
            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedOrders(),
            'comments_count' => $this->comments()->serviceContext(true)->count(),
            'color' => $this->color,
            'gvwr' => $this->gvwr,
        ];
    }
}
