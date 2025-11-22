<?php

namespace App\Http\Controllers\Api\OneC\Permissions;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OneC\Permissions\PermissionsListResource;
use App\Http\Resources\Api\OneC\Permissions\RolesResource;
use App\Models\OneC\Moderator;
use App\Models\Permissions\Role;
use Core\Services\Permissions\PermissionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Permissions
 */
class PermissionsController extends Controller
{
    /**
     * List
     *
     * Get list of all available permissions for current 1c API user
     *
     * @responseFile docs/api/permissions/permissions.json
     */
    public function list(PermissionService $permissionService): AnonymousResourceCollection
    {
        return PermissionsListResource::collection(
            $permissionService
                ->getGroupsFor(Moderator::GUARD)
                ->toArray()
        );
    }

    /**
     * Roles
     *
     * Get all available roles for 1c API
     *
     * @responseFile docs/api/permissions/roles.json
     */
    public function roles(): AnonymousResourceCollection
    {
        return RolesResource::collection(
            Role::query()
                ->with('permissions')
                ->where('guard_name', Moderator::GUARD)
                ->paginate()
        );
    }
}
