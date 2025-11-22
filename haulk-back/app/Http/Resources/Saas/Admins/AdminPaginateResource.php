<?php

namespace App\Http\Resources\Saas\Admins;

use App\Http\Resources\Saas\Permissions\PermissionForItemResource;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminDelete;
use App\Permissions\Admins\AdminShow;
use App\Permissions\Admins\AdminUpdate;
use App\Permissions\SimplePermission;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Admin
 */
class AdminPaginateResource extends JsonResource
{
    public function toArray($request): array
    {
        $permissionItem = [
            AdminShow::KEY => SimplePermission::SHOW,
            AdminUpdate::KEY => SimplePermission::UPDATE,
            AdminDelete::KEY => SimplePermission::DELETE,
        ];

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'permission' => PermissionForItemResource::make($permissionItem)
        ];
    }
}

/**
 * @OA\Schema(schema="AdminPaginatedResource",
 *    @OA\Property(property="data", description="Admin paginated list", type="array",
 *        @OA\Items(ref="#/components/schemas/AdminResourceRaw")
 *    ),
 *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 */
