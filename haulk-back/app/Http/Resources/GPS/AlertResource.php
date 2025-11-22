<?php

namespace App\Http\Resources\GPS;

use App\Http\Resources\Tags\TagShortResource;
use App\Models\GPS\Alert;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Alert
 */
class AlertResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="GPSAlertRaw",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "received_at", "alert_type"},
     *             @OA\Property(property="id", type="integer", description="Alert id"),
     *             @OA\Property(property="received_at", type="integer", description="Alert received at, timestamp"),
     *             @OA\Property(property="alert_type", type="string", description="Alert type: speeding, device_connection, device_battery, device_connection_restored, device_connection_lost"),
     *             @OA\Property(property="vehicle_unit_number", type="string", description="Vehicle unit number"),
     *             @OA\Property(property="driver_name", type="string", description="Driver name"),
     *             @OA\Property(property="speed", type="string", description="Speed (miles)"),
     *             @OA\Property(property="delails", type="string", description="Alert details"),
     *             @OA\Property(property="last_driving_at", type="integer", description="Vehicle last driving at, timestamp"),
     *             @OA\Property(property="location", type="object",
     *                 @OA\Property(property="lat", type="float", example=49.069782),
     *                 @OA\Property(property="lng", type="float", example=28.632826),
     *             ),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="GPSAlertPaginated",
     *     @OA\Property(
     *         property="data",
     *         description="Alerts paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/GPSAlertRaw")
     *     ),
     *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     *
     * @OA\Schema(
     *      schema="GPSAlertList",
     *      @OA\Property(
     *          property="data",
     *          description="Alerts paginated list",
     *          type="array",
     *          @OA\Items(ref="#/components/schemas/GPSAlertRaw")
     *      ),
     *  )
     */
    public function toArray($request)
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->truck ?: $this->trailer;

        return [
            'id' => $this->id,
            'received_at' => $this->received_at->timestamp,
            'alert_type' => $this->alert_type,
            'vehicle_unit_number' => $vehicle->unit_number ?? null,
            'driver_name' => $this->driver ? $this->driver->full_name : null,
            'details' => $this->getDetailsMessage(),
            'speed' => round($this->speed, 2),
            'last_driving_at' => isset($vehicle->last_driving_at)
                ? $vehicle->last_driving_at->timestamp
                : null,
            'location' => [
                'lat' => $this->latitude,
                'lng' => $this->longitude
            ],
        ];
    }
}
