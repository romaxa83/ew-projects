<?php

namespace App\Http\Resources\Reports;

use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Users\UserMiniResource;
use App\Models\Reports\DriverTripReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DriverTripReport
 */
class DriverTripReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="DriverTripReportResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        description="data",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer", description=""),
     *                @OA\Property(property="driver_id", type="integer", description=""),
     *                @OA\Property(property="driver", type="object", description="Order driver", allOf={@OA\Schema(ref="#/components/schemas/UserMini")}),
     *                @OA\Property(property="file", type="object", description="file", allOf={@OA\Schema(ref="#/components/schemas/FileRaw")}),
     *                @OA\Property(property="report_date", type="integer", description=""),
     *                @OA\Property(property="date_from", type="integer", description=""),
     *                @OA\Property(property="date_to", type="integer", description="")
     *            )
     *        }
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id ? (int) $this->id : null,
            'driver_id' => $this->driver_id ? (int) $this->driver_id : null,
            'driver' => UserMiniResource::make($this->driver),
            'file' => FileResource::make($this->getFirstMedia(DriverTripReport::DRIVER_FILE_COLLECTION_NAME)),

            'report_date' => $this->report_date ? $this->report_date->timestamp : null,
            'date_from' => $this->date_from ? $this->date_from->timestamp : null,
            'date_to' => $this->date_to ? $this->date_to->timestamp : null,
        ];
    }
}
