<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyReportTotalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="CompanyReportTotalResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Report data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"full_name"},
     *                        @OA\Property(property="total_due", type="number", description=""),
     *                        @OA\Property(property="current_due", type="number", description=""),
     *                        @OA\Property(property="past_due", type="number", description=""),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'total_due' => (double) $this['total_due'],
            'current_due' => (double) $this['current_due'],
            'past_due' => (double) $this['past_due'],
        ];
    }
}
