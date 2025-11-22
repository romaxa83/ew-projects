<?php

namespace App\Http\Resources\Permissions;

use App\Foundations\Modules\Permission\Models\Role;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="RoleResource",
 *     type="object",
 *     allOf={@OA\Schema(
 *          required={"id", "name"},
 *          @OA\Property(property="id", type="integer", example=1),
 *          @OA\Property(property="name", type="string", example="admin"),
 *         )
 *     }
 * )
 *
 * @mixin Role
 */
class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => __('permissions.roles.' . $this->name),
        ];
    }
}

