<?php

namespace App\Http\Resources\Saas\GPS\History;

use App\Http\Resources\GPS\AlertResource;
use App\Http\Resources\Users\DriversListResource;
use App\Models\GPS\History;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin History
 */
class HistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $vehicle = $this->truck ?? $this->trailer;

        return [
            'id' => $this->id,
            'received_at' => $this->received_at->timestamp,
            'created_at' => $this->created_at->timestamp,
            'location' => [
                'lat' => $this->latitude,
                'lng' => $this->longitude
            ],
            'speed' => round($this->speed, 2),
            'vehicle_mileage' => $this->vehicle_mileage,
            'heading' => $this->heading,
            'event_type' => $this->event_type,
            'event_duration' => convert_sec_to_hour_and_min($this->event_duration),
            'device_battery_level' => $this->device_battery_level,
            'device_battery_charging_status' => $this->device_battery_charging_status,
            'unit_number' => $vehicle->unit_number ?? null,
            'driver' => DriversListResource::make($this->driver),
            'old_driver' => DriversListResource::make($this->oldDriver),
            'alerts' => AlertResource::collection($this->alerts),
        ];
    }
}

/**
 * @OA\Schema(schema="HistoryDeviceRawResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="received_at", type="integer"),
 *             @OA\Property(property="created_at", type="integer"),
 *             @OA\Property(property="location", type="object",
 *                   @OA\Property(property="lat", type="float", example=49.069782),
 *                   @OA\Property(property="lng", type="float", example=28.632826),
 *              ),
 *             @OA\Property(property="speed", type="string"),
 *             @OA\Property(property="vehicle_mileage", type="string"),
 *             @OA\Property(property="heading", type="string"),
 *             @OA\Property(property="event_type", type="string"),
 *             @OA\Property(property="event_duration", type="string"),
 *             @OA\Property(property="device_battery_level", type="string"),
 *             @OA\Property(property="device_battery_charging_status", type="string"),
 *             @OA\Property(property="unit_number", type="string"),
 *             @OA\Property(property="driver", ref="#/components/schemas/DriversListResource"),
 *             @OA\Property(property="old_driver", description="The driver who was driving before the driver change",
 *                 ref="#/components/schemas/DriversListResource"),
 *             @OA\Property(property="alerts", ref="#/components/schemas/GPSAlertRaw"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="HistoryDeviceResource", type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/HistoryDeviceRawResource")
 *         }
 *     )
 * )
 *
 * @OA\Schema(schema="HistoryDevicePaginatedResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/HistoryDeviceRawResource")
 *     ),
 *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 *
 */

