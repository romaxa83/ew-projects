<?php

namespace App\Http\Controllers\V1\Saas\Admins;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\Admins\AdminFilterRequest;
use App\Http\Requests\Saas\Admins\AdminRequest;
use App\Http\Resources\Saas\Admins\AdminPaginateResource;
use App\Http\Resources\Saas\Admins\AdminResource;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminCreate;
use App\Permissions\Admins\AdminDelete;
use App\Permissions\Admins\AdminList;
use App\Permissions\Admins\AdminShow;
use App\Permissions\Admins\AdminUpdate;
use App\Services\Saas\Admins\AdminService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AdminController extends ApiController
{

    private AdminService $service;

    public function __construct(AdminService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param AdminFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(AdminFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize(AdminList::KEY);

        $admins = Admin::filter($request->validated())
            ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

        return AdminPaginateResource::collection($admins);
    }

    /**
     * @param Admin $admin
     * @return AdminResource
     * @throws AuthorizationException
     */
    public function show(Admin $admin): AdminResource
    {
        $this->authorize(AdminShow::KEY);

        return AdminResource::make($admin);
    }

    /**
     * @param AdminRequest $request
     * @return AdminResource
     * @throws AuthorizationException
     */
    public function store(AdminRequest $request): AdminResource
    {
        $this->authorize(AdminCreate::KEY);

        $admin = $this->service->create($request->getDto());

        return AdminResource::make($admin);
    }

    /**
     * @param AdminRequest $request
     * @param Admin $admin
     * @return AdminResource
     * @throws AuthorizationException
     */
    public function update(AdminRequest $request, Admin $admin): AdminResource
    {
        $this->authorize(AdminUpdate::KEY);

        $admin = $this->service->update($admin, $request->getDto());

        return AdminResource::make($admin);
    }

    /**
     * @param Admin $admin
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function destroy(Admin $admin): JsonResponse
    {
        $this->authorize(AdminDelete::KEY);

        $this->service->delete($admin);

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }
}

/**
 * @OA\Get(path="/v1/saas/admins",
 *     tags={"Admins"}, summary="Get admin paginated list", operationId="Get admins data", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
 *          @OA\Schema(type="integer", default="1")
 *     ),
 *     @OA\Parameter(name="per_page", in="query", description="Admin per page", required=false,
 *          @OA\Schema(type="integer", default="10")
 *     ),
 *     @OA\Parameter(name="order", in="query", description="Field for sort", required=false,
 *          @OA\Schema(type="string", default="status", enum={"full_name","email","phone"})
 *     ),
 *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
 *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
 *     ),
 *     @OA\Parameter(name="roles", in="query", @OA\Schema(type="array",
 *              @OA\Items(allOf={@OA\Schema(type="integer")})
 *          )
 *     ),
 *     @OA\Parameter(name="query", in="query", required=false, @OA\Schema(type="string", default="null",)),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AdminPaginatedResource")
 *     ),
 * )
 */

/**
 * @OA\Get(path="/v1/saas/admins/{adminId}", tags={"Admins"}, summary="Get admin info",
 *     operationId="Get admin data", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
 *     ),
 * )
 */

/**
 * @OA\Post(path="/v1/saas/admins", tags={"Admins"}, summary="Create admin", operationId="Create admin", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(name="full_name", in="query", description="Admin full name", required=true,
 *          @OA\Schema(type="string", default="Vlad Chernenko",)
 *     ),
 *     @OA\Parameter(name="email", in="query", description="Admin email", required=true,
 *          @OA\Schema(type="string", default="chernenko.v@wezom.com.ua",)
 *     ),
 *     @OA\Parameter(name="role_id", in="query", description="Admin role id", required=true,
 *          @OA\Schema(type="integer", default="null",)
 *     ),
 *     @OA\Parameter(name="phone", in="query", description="Admin phone", required=false,
 *          @OA\Schema(type="string", default="1234567",)
 *     ),
 *
 *     @OA\Response(response=201, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
 *     ),
 * )
 */

/**
 * @OA\Put(path="/v1/saas/admins/{adminId}", tags={"Admins"}, summary="Update admin", operationId="Update admin", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *
 *     @OA\Parameter(name="id", in="path", description="Admin id", required=true,
 *          @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(name="full_name", in="query", description="Admin full name", required=true,
 *          @OA\Schema(type="string", default="Vlad Chernenko",)
 *     ),
 *     @OA\Parameter(name="email", in="query", description="Admin email", required=true,
 *          @OA\Schema(type="string", default="chernenko.v@wezom.com.ua",)
 *     ),
 *     @OA\Parameter(name="role_id", in="query", description="Admin role id", required=true,
 *          @OA\Schema(type="integer", default="null",)
 *     ),
 *     @OA\Parameter(name="phone", in="query", description="Admin phone", required=false,
 *          @OA\Schema(type="string", default="1234567",)
 *     ),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
 *     ),
 * )
 */

/**
 * @OA\Delete(path="/v1/saas/admins/{adminId}", tags={"Admins"}, summary="Delete admin", operationId="Delete admin", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(response=204, description="Successful operation",),
 * )
 */
