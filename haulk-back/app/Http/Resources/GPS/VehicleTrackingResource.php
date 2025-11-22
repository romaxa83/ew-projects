<?php

namespace App\Http\Resources\GPS;

use App\Entities\Saas\GPS\TrackingEntity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TrackingEntity
 */
class VehicleTrackingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'speed' => round($this->speed, 2),
            'location' => [
                'lat' => $this->latitude,
                'lng' => $this->longitude
            ],
            'event_type' => $this->type,
            'event_duration' => $this->typeDuration,
            'vehicle_unit_number' => $this->unitNumber,
            'driver_name' => $this->driverName,
            'last_driving_at' => $this->lastDrivingAt,
            'alerts' => AlertSimpleResource::collection($this->alerts),
        ];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="VehicleGPSTrackingRaw",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", description="ID model (truck or trailer)"),
     *             @OA\Property(property="speed", type="float", description="Speed (miles)"),
     *             @OA\Property(property="location", type="object",
     *                  @OA\Property(property="lat", type="float", example=49.069782),
     *                  @OA\Property(property="lng", type="float", example=28.632826),
     *             ),
     *             @OA\Property(property="event_type", type="string", description="Event type:driving, idle, long_idle, engine_off, for trailer: driving, stooped"),
     *             @OA\Property(property="event_duration", type="integer", description="event duration for second"),
     *             @OA\Property(property="vehicle_unit_number", type="string", description="Vehicle unit number"),
     *             @OA\Property(property="driver_name", type="string", description="Driver name"),
     *             @OA\Property(property="last_driving_at", type="integer", description="Vehicle last driving at, timestamp"),
     *             @OA\Property(property="alerts", type="array",
     *                      @OA\Items(ref="#/components/schemas/GPSAlertSimpleRaw")
     *            )
     *         )
     *     }
     * )
     *
     * @OA\Schema(schema="VechicleGPSTrackingResource", type="object",
     *      @OA\Property(
     *          property="data",
     *          type="object",
     *          allOf={
     *              @OA\Schema(ref="#/components/schemas/VehicleGPSTrackingRaw")
     *          }
     *      )
     *  )
     */
}
