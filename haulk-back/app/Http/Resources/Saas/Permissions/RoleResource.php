<?php

namespace App\Http\Resources\Saas\Permissions;

use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleDelete;
use App\Permissions\Roles\RoleShow;
use App\Permissions\Roles\RoleUpdate;
use App\Permissions\SimplePermission;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Role
 */
class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        $permissionItem = [
            RoleShow::KEY => SimplePermission::SHOW,
            RoleUpdate::KEY => SimplePermission::UPDATE,
            RoleDelete::KEY => SimplePermission::DELETE,
        ];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->relationLoaded('permissions')
                ? $this->permissions
                    ->pluck('name')
                    ->toArray()
                : null,
            'permission' => PermissionForItemResource::make($permissionItem)
        ];
    }
}

/**
 * @OA\Schema(schema="RolePaginatedResource",
 *    @OA\Property(property="data", description="Role paginated list", type="array",
 *        @OA\Items(ref="#/components/schemas/RoleResourceRaw")
 *    ),
 *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 */

/**
 * @OA\Schema(schema="RoleResourceRaw", type="object", allOf={
 *     @OA\Schema(
 *                @OA\Property(property="id", type="integer"),
 *                @OA\Property(property="name", type="string"),
 *                @OA\Property(property="permissions",
 *                      @OA\Schema(type="array",
 *                          @OA\Items(allOf={@OA\Schema(type="string")})
 *                      )
 *                ),
 *                @OA\Property(property="permission", type="array",
 *                      @OA\Items(ref="#/components/schemas/PermissionForItemRaw")
 *                ),
 *            )
 *        }
 * )
 */

/**
 * @OA\Schema(schema="RoleResource", type="object",
 *        @OA\Property(property="data", type="object",
 *              allOf={@OA\Schema(
 *                @OA\Property(property="id", type="integer"),
 *                @OA\Property(property="name", type="string"),
 *                @OA\Property(property="permissions", type="array",
 *                      @OA\Items(allOf={@OA\Schema(type="string", example="admin.create|admin.update|admin.delete")})
 *                ),
 *            )}),
 * )
 */
