<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Foundations\Modules\Permission\Repositories\RoleRepository;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Permissions\RoleResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoleController extends ApiController
{
    public function __construct(
        protected RoleRepository $repo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/roles",
     *     tags={"Roles"},
     *     security={{"Basic": {}}},
     *     summary="Get roles list without pagination",
     *     operationId="GetRolesListWithoutPagination",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Data without pagination",
     *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function list(): ResourceCollection
    {
        return RoleResource::collection($this->repo->list());
    }
}

