<?php

namespace App\Http\Resources\Saas\GPS;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Repositories\Saas\GPS\DeviceRepository;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeviceSubscription
 */
class GPSDeviceSubscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'start_at' => $this->activate_at->timestamp ?? null,
            'end_at' => $this->canceled_at->timestamp ?? null,
            'active_till_at' => $this->activate_till_at->timestamp ?? null,
            'access_till_at' => $this->access_till_at->timestamp ?? null,
            'total_device' => $this->devicesCount(),
            'total_active' => $this->status->isDraft() || $this->status->isCanceled()
                ? null
                : $this->devicesCountByStatus(DeviceStatus::ACTIVE())
            ,
            'total_inactive_device' => $this->devicesCountByStatus(DeviceStatus::INACTIVE()),
            'total_deleted_device' => $this->devicesCountByStatus(DeviceStatus::DELETED()),
            'has_active_at_vehicle' => $this->activeAtVehicle(),
            'current_rate' => $this->company->isExclusivePlan()
                ? 0
                :$this->current_rate,
            'next_rate' => $this->company->isExclusivePlan()
                ? 0
                : $this->next_rate
        ];
    }

    private function activeAtVehicle()
    {
        /** @var $repo DeviceRepository */
        $repo = resolve(DeviceRepository::class);

        return $repo->hasActiveAtVehicle($this->company_id);
    }
}

/**
 *
 * @OA\Schema(schema="GpsDeviceSubscriptionRawResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="status", type="string", enum={"active", "canceled"}),
 *             @OA\Property(property="start_at", type="integer"),
 *             @OA\Property(property="active_till_at", type="integer"),
 *             @OA\Property(property="end_at", type="integer"),
 *             @OA\Property(property="access_till_at", type="integer"),
 *             @OA\Property(property="total_device", type="integer"),
 *             @OA\Property(property="total_active_device", type="integer"),
 *             @OA\Property(property="total_inactive_device", type="integer"),
 *             @OA\Property(property="has_active_at_vehicle", type="boolean", description="Check if the vehicle has active devices"),
 *             @OA\Property(property="current_rate", type="float", description="Current rate per device"),
 *             @OA\Property(property="next_rate", type="float", description="Rate per device for next billing period"),
 *         )
 *     }
 * )
 *
 *
 * @OA\Schema(schema="GpsDeviceSubscriptionResource", type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/GpsDeviceSubscriptionRawResource")
 *         }
 *     )
 * )
 *
 * @OA\Schema(schema="GpsDeviceSubscriptionPaginatedResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/GpsDeviceSubscriptionRawResource")
 *     ),
 *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 *
 */

