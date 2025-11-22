<?php

namespace App\Http\Resources\Api\OneC\Permissions;

use App\Models\Permissions\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Role
 */
class RolesResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'translation' => RoleTranslationResource::make($this->translation),
            'permissions' => PermissionsListResource::collection($this->permissions),
        ];
    }
}
