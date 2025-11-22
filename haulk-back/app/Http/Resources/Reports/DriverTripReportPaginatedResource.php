<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverTripReportPaginatedResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return DriverTripReportResource
     *
     * @OA\Schema(
     *    schema="DriverTripReportPaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        description="",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/DriverTripReportResource")
     *    ),
     *    @OA\Property(
     *        property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property(
     *        property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return DriverTripReportResource::make($this);
    }
}
