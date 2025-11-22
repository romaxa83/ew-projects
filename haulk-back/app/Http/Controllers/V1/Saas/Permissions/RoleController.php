<?php

namespace App\Http\Controllers\V1\Saas\Permissions;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\Permissions\RoleRequest;
use App\Http\Resources\Saas\Permissions\PermissionGroupResource;
use App\Http\Resources\Saas\Permissions\RoleResource;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleCreate;
use App\Permissions\Roles\RoleDelete;
use App\Permissions\Roles\RoleList;
use App\Permissions\Roles\RoleShow;
use App\Permissions\Roles\RoleUpdate;
use App\Services\Permissions\PermissionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use Throwable;

class RoleController extends ApiController
{

    public const GUARD = Admin::GUARD;

    private PermissionService $service;

    public function __construct(PermissionService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize(RoleList::KEY);

        $roles = Role::whereGuardName(self::GUARD)
            ->paginate();

        return RoleResource::collection($roles);
    }

    /**
     * @param int $id
     * @return RoleResource
     * @throws AuthorizationException
     */
    public function show(int $id): RoleResource
    {
        $this->authorize(RoleShow::KEY);

        $role = $this->getRoleByIdAndGuard($id, static::GUARD);

        return RoleResource::make(
            $role->load('permissions')
        );
    }

    protected function getRoleByIdAndGuard(int $id, string $guard): Role
    {
        return Role::query()
            ->whereGuardName($guard)
            ->findOrFail($id);
    }

    /**
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function permissions(): AnonymousResourceCollection
    {
        $this->authorize(RoleShow::KEY);

        return PermissionGroupResource::collection(
            $this->service->getGroupsFor(static::GUARD)
                ->values()
        );
    }

    /**
     * @param RoleRequest $request
     * @return RoleResource|JsonResponse
     * @throws AuthorizationException
     */
    public function store(RoleRequest $request)
    {
        $this->authorize(RoleCreate::KEY);

        try {
            $role = $this->service->createRole($request->getDto(), self::GUARD);

            return RoleResource::make(
                $role->load('permissions')
            );
        } catch (Throwable $ex) {
            Log::error($ex);

            return $this->makeErrorResponse($ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param RoleRequest $request
     * @param int $id
     * @return RoleResource|JsonResponse
     * @throws AuthorizationException
     */
    public function update(RoleRequest $request, int $id)
    {
        $this->authorize(RoleUpdate::KEY);

        $guard = self::GUARD;
        $role = $this->getRoleByIdAndGuard($id, $guard);

        try {
            $role = $this->service->updateRole($role, $request->getDto(), $guard);

            return RoleResource::make(
                $role->load('permissions')
            );
        } catch (Throwable $ex) {
            Log::error($ex);

            return $this->makeErrorResponse($ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize(RoleDelete::KEY);

        $role = $this->getRoleByIdAndGuard($id, static::GUARD);

        try {
            $this->service->deleteRole($role);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $ex) {
            Log::error($ex);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}


/**
 * @OA\Get(path="/v1/saas/roles",
 *     tags={"Admin roles"}, summary="Get roles paginated list", operationId="Get roles data", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *
 *     @OA\Parameter(name="per_page", in="query", description="Roles per page", required=false,
 *          @OA\Schema(type="integer", default="10")
 *     ),
 *     @OA\Parameter(name="order", in="query", description="Field for sort", required=false,
 *          @OA\Schema(type="string", default="status", enum={"full_name","email","phone"})
 *     ),
 *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
 *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
 *     ),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/RolePaginatedResource")
 *     ),
 * )
 */

/**
 * @OA\Get(path="/v1/saas/roles/{roleId}", tags={"Admin roles"}, summary="Get role info", operationId="Get role data", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *
 *     @OA\Parameter(name="id", in="path", description="Admin role id", required=true,
 *          @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
 *     ),
 * )
 */

/**
 * @OA\Get(path="/v1/saas/roles/permissions", tags={"Admin roles"}, summary="Get all permissions for admin panel", operationId="Get all permissions for admin panel", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/PermissionGroupResource")
 *     ),
 * )
 */

/**
 * @OA\Post(path="/v1/saas/roles", tags={"Admin roles"}, summary="Create admin role", operationId="Create admin role", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *
 *     @OA\Parameter(name="name", in="query", description="Admin Role name", required=true,
 *          @OA\Schema(type="string", example="User manager",)
 *     ),
 *     @OA\Parameter(name="permissions", in="query",
 *          @OA\Schema(type="array", @OA\Items(allOf={@OA\Schema(type="string")}))
 *     ),
 *
 *     @OA\Response(response=201, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
 *     ),
 * )
 */

/**
 * @OA\Put(path="/v1/saas/roles/{roleId}", tags={"Admin roles"}, summary="Update role", operationId="Update role", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *
 *     @OA\Parameter(name="id", in="path", description="Admin role id", required=true,
 *          @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(name="name", in="query", description="Admin role name", required=true,
 *          @OA\Schema(type="string", example="User manager",)
 *     ),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
 *     ),
 * )
 */

/**
 * @OA\Delete(path="/v1/saas/roles/{roleId}", tags={"Admin roles"}, summary="Delete role", operationId="Delete role", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(response=204, description="Successful operation",),
 * )
 */
