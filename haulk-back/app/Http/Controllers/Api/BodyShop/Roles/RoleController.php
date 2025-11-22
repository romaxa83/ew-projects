<?php

namespace App\Http\Controllers\Api\BodyShop\Roles;

use App\Http\Controllers\ApiController;
use App\Http\Resources\BodyShop\Roles\RoleResource;
use App\Models\Users\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    /**
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/roles-list",
     *     tags={"Roles Body Shop"},
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
     *         @OA\JsonContent(ref="#/components/schemas/RolesListBS")
     *     ),
     * )
     */
    public function list()
    {
        return RoleResource::collection(Role::whereIn('name', User::BS_ROLES)->get());
    }
}
