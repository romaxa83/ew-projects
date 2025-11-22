<?php

namespace App\Http\Resources\GPS;

use App\Entities\Saas\GPS\TrackingEntity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TrackingEntity
 */
class TrackingResource extends JsonResource
{
    public function toArray($request)
    {
        if($this->isTruck){
            $data['truck'] = VehicleTrackingResource::make($this);
            $data['trailer'] = VehicleTrackingResource::make($this->trailer);
        } else {
            $data['truck'] = null;
            $data['trailer'] = VehicleTrackingResource::make($this);
        }

        return $data;
    }

    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="GPSTrackingRaw",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *            @OA\Property(property="truck", ref="#/components/schemas/GPSTrackingVehicleRaw"),
     *            @OA\Property(property="trailer", ref="#/components/schemas/GPSTrackingVehicleRaw"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(schema="GPSTrackingResource", type="object",
     *      @OA\Property(
     *          property="data",
     *          type="object",
     *          allOf={
     *              @OA\Schema(ref="#/components/schemas/GPSTrackingRaw")
     *          }
     *      )
     *  )
     *
     * @OA\Schema(
     *      schema="GPSTrackingVehicleRaw",
     *      type="object",
     *      allOf={
     *          @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="ID model (truck or trailer)"),
     *              @OA\Property(property="location", type="object",
     *                   @OA\Property(property="lat", type="float", example=49.069782),
     *                   @OA\Property(property="lng", type="float", example=28.632826),
     *              ),
     *              @OA\Property(property="event_type", type="string", description="Event type:driving, idle, long_idle, engine_off, for trailer: driving, stooped"),
     *              @OA\Property(property="event_duration", type="integer", description="evet duration for second"),
     *              @OA\Property(property="vehicle_unit_number", type="string", description="Vehicle unit number"),
     *              @OA\Property(property="driver_name", type="string", description="Driver name"),
     *              @OA\Property(property="last_driving_at", type="integer", description="Vehicle last driving at, timestamp"),
     *              @OA\Property(property="alerts", type="array",
     *                       @OA\Items(ref="#/components/schemas/GPSAlertSimpleRaw")
     *             ),
     *          )
     *      }
     *  )
     *
     */
}
