<?php

namespace App\Http\Resources\BodyShop\Vehicles\Trucks;

use App\Http\Resources\Tags\TagShortResource;
use App\Models\Vehicles\Truck;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Truck
 */
class TruckPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TruckRawBS",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "vin", "unit_number", "year", "make", "model", "type"},
     *             @OA\Property(property="id", type="integer", description="Truck id"),
     *             @OA\Property(property="vin", type="string", description="Truck vin"),
     *             @OA\Property(property="unit_number", type="string", description="Truck Unit number"),
     *             @OA\Property(property="license_plate", type="string", description="Truck Licence plate"),
     *             @OA\Property(property="temporary_plate", type="string", description="Truck Temporary plate"),
     *             @OA\Property(property="make", type="string", description="Truck make"),
     *             @OA\Property(property="model", type="string", description="Truck model"),
     *             @OA\Property(property="year", type="string", description="Truck year"),
     *             @OA\Property(property="type", type="integer", description="Truck type"),
     *             @OA\Property(property="owner_name", type="string", description="Truck owner name"),
     *             @OA\Property(property="customer_id", type="integer", description="Truck customer id"),
     *             @OA\Property(property="driver_name", type="string", description="Truck driver name"),
     *             @OA\Property(property="tags", type="array", description="Truck tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *             @OA\Property(property="company_name", type="string", description="Truck company name"),
     *             @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Is truck has related open orders"),
     *             @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Is truck has related deleted orders"),
     *             @OA\Property(property="comments_count", type="int", description="Comments count"),
     *             @OA\Property(property="color", type="string", description="Color"),
     *             @OA\Property(property="gvwr", type="number", description="GVWR"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="TruckPaginateBS",
     *     @OA\Property(
     *         property="data",
     *         description="Truck paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/TruckRawBS")
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
            'type' => $this->type,
            'owner_name' => $this->getOwnerFullName(),
            'customer_id' => $this->customer_id ?? null,
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
