<?php


namespace App\Http\Controllers\Api\Permissions;


use App\Http\Controllers\ApiController;
use App\Http\Resources\Permissions\PermissionGridResource;
use App\Models\Users\User;
use App\Services\Permissions\PermissionWorker;
use Exception;

class PermissionController extends ApiController
{
    /**
     * @param string $roleName
     * @return PermissionGridResource
     *
     * @OA\Get(
     *     path="/api/permissions/{roleName}",
     *     tags={"Permissions"},
     *     summary="Get permission grid for role",
     *     operationId="Get permission grid for role",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="roleName",
     *          in="path",
     *          description="Dispatcher id",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="Dispatcher",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PermissionGrid")
     *     ),
     * )
     * @throws Exception
     */
    public function show(string $roleName = User::DISPATCHER_ROLE)
    {
        $service = new PermissionWorker();
        $service->setPermissionGrid(null, $roleName);
        return new PermissionGridResource($service->getPermissionsGrid());
    }
}