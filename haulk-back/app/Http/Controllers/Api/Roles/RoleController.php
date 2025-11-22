<?php

namespace App\Http\Controllers\Api\Roles;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Roles\RoleResource;
use App\Models\Users\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    /**
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/roles-list",
     *     tags={"Roles"},
     *     summary="Get roles list without pagination",
     *     operationId="Get roles data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/RolesList")
     *     ),
     * )
     */
    public function list()
    {
        return RoleResource::collection(Role::whereIn('name', User::COMPANY_ROLES)->get());
    }

    /**
     * @param Role $role
     * @return RoleResource
     *
     * @OA\Get(
     *     path="/api/roles/{roleId}",
     *     tags={"Roles"},
     *     summary="Get info about role",
     *     operationId="Get role data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Role")
     *     ),
     * )
     */
    public function show(Role $role)
    {
        return new RoleResource($role);
    }
}
