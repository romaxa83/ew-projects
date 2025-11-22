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
class AlertSimpleResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="GPSAlertSimpleRaw",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"alert_type"},
     *             @OA\Property(property="alert_type", type="string", description="Alert type: speeding, device_connection, device_battery"),
     *             @OA\Property(property="delails", type="string", description="Alert details"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(schema="GPSAlertResource", type="object",
     *       @OA\Property(
     *           property="data",
     *           type="object",
     *           allOf={
     *               @OA\Schema(ref="#/components/schemas/GPSAlertSimpleRaw")
     *           }
     *       )
     *   )
     */
    public function toArray($request)
    {
        return [
            'alert_type' => $this->alert_type,
            'details' => $this->getDetailsMessage(),
        ];
    }
}
