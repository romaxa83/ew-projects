<?php

namespace App\Http\Resources\Saas\Admins;

use App\Http\Resources\Files\ImageResource;
use App\Models\Admins\Admin;
use App\Models\Language;
use App\Services\Permissions\PermissionWorker;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Admin
 */
class AdminProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        $permissions = resolve(PermissionWorker::class);

        return [
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,
            'full_name' => $this->full_name,
            'role_id' => ($this->roles && $this->roles->first())
                ? $this->roles->first()->id
                : null,
            $this->getImageField() => ImageResource::make($this->getFirstImage()),
            'language' => $this->language ?? Language::default()->first()->slug,
            'permissions' => $permissions->getPermissionsAdmin(
                $permissions->getAdminPermissions(Admin::find($this->id))
            )
        ];
    }
}

/**
 * @OA\Schema(schema="AdminProfile", type="object",
 *     @OA\Property(property="data", type="object", description="Profile data", allOf={
 *          @OA\Schema(required={"id", "full_name", "email"},
 *               @OA\Property(property="id", type="integer", description="User id"),
 *               @OA\Property(property="email", type="string", description="User email"),
 *               @OA\Property(property="phone", type="string", description="User phone"),
 *               @OA\Property(property="full_name", type="string", description="User name"),
 *               @OA\Property(property="role_id", type="integer", description="Role id"),
 *               @OA\Property(property="photo", type="object", description="image with different size", allOf={
 *                   @OA\Schema(ref="#/components/schemas/Image")
 *               }),
 *               @OA\Property(property="language", type="string",),
 *          )
 *     }),
 * )
 */
