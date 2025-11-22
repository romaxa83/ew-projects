<?php

namespace App\Http\Resources\Saas\GPS\Device;

use App\Http\Resources\Vehicles\Trailers\TrailerSimpleResource;
use App\Http\Resources\Vehicles\Trucks\TruckSimpleResource;
use App\Models\Saas\GPS\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Device
 */
class DeviceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_device_name' => $this->company_device_name,
            'imei' => $this->imei,
            'status' => $this->status,
            'status_activate_request' => $this->status_activate_request,
            'status_request' => $this->status_request,
            'phone' => $this->phone ? $this->phone->getValue() : null,
            'company' => $this->company
                ? [
                    'name' => $this->company->name,
                    'id' => $this->company->id,
                ]
                : null,
            'active_at' => $this->active_at ? $this->active_at->timestamp : null,
            'inactive_at' => $this->inactive_at ? $this->inactive_at->timestamp : null,
            'force_deleted_at' => $this->status->isDeleted()
                ? $this->deleted_at->addDays(Device::DAYS_TO_FORCE_DELETE)->timestamp
                : null,
            'truck' => TruckSimpleResource::make($this->truck),
            'trailer' => TrailerSimpleResource::make($this->trailer),
            'active_till_at' => $this->active_till_at ? $this->active_till_at->timestamp : null,
        ];
    }
}

/**
 *
 * @OA\Schema(schema="CompanyForDeviceResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="DeviceRawResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="company_device_name", type="string"),
 *             @OA\Property(property="imei", type="string"),
 *             @OA\Property(property="status", type="string", enum={"active", "inactive", "deleted"}),
 *             @OA\Property(property="status_activate_request", type="string", description="Is there an activation/deactivation request and what is its status", enum={"activate", "deactivate", "none"}),
 *             @OA\Property(property="status_request", type="string", description="Request status, for backoffice", enum={"pending", "closed", "none", "cancel_subscription"}),
 *             @OA\Property(property="phone", type="string"),
 *             @OA\Property(property="active_at", type="integer"),
 *             @OA\Property(property="inactive_at", type="integer"),
 *             @OA\Property(property="active_till_at", type="integer", description="Until this time, the device was still activated after deactivation."),
 *             @OA\Property(property="force_deleted_at", type="integer", description="Date when the device will be force deleted"),
 *             @OA\Property(property="company", ref="#/components/schemas/CompanyForDeviceResource",),
 *             @OA\Property(property="truck", ref="#/components/schemas/TruckSimpleResource",),
 *             @OA\Property(property="trailer", ref="#/components/schemas/TrailerSimpleResource",),
 *         )
 *     }
 * )
 *
 *
 * @OA\Schema(schema="DeviceResource", type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/DeviceRawResource")
 *         }
 *     )
 * )
 *
 * @OA\Schema(schema="DevicePaginatedResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/DeviceRawResource")
 *     ),
 *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 *
 */
