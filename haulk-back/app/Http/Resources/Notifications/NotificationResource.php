<?php

namespace App\Http\Resources\Notifications;

use App\Http\Resources\Vehicles\Trailers\TrailerSimpleResource;
use App\Http\Resources\Vehicles\Trucks\TruckSimpleResource;
use App\Models\Notifications\Notification;
use App\Models\Saas\GPS\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'message' => __($this->message_key, $this->message_attr),
            'status' => $this->status,
            'type' => $this->type,
            'place' => $this->place,
            'read_at' => $this->read_at ? $this->read_at->timestamp : null,
            'created_at' => $this->created_at->timestamp,
            'meta' => $this->meta,
            'action' => $this->action,
        ];
    }
}

/**
 *
 * @OA\Schema(schema="NotificationRawResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="status", type="string", enum={"new", "read"}),
 *             @OA\Property(property="type", type="string", enum={"gps"}),
 *             @OA\Property(property="place", type="string", enum={"backoffice"}),
 *             @OA\Property(property="read_at", type="integer"),
 *             @OA\Property(property="created_at", type="integer"),
 *             @OA\Property(property="meta", type="array", @OA\Items()),
 *             @OA\Property(property="action", type="string", enum={"none", "to_device_cancel_subscription", "to_device_active_till"}),
 *         )
 *     }
 * )
 *
 *
 * @OA\Schema(schema="NotificationResource", type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/NotificationRawResource")
 *         }
 *     )
 * )
 *
 * @OA\Schema(schema="NotificationPaginatedResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/NotificationRawResource")
 *     ),
 *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 *
 */

