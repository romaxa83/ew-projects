<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Foundations\Modules\Permission\Repositories\RoleRepository;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UserFilterRequest;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\UserShortListRequest;
use App\Http\Resources\Users\UserPaginationResource;
use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Models\Users\User;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Repositories\Users\UserRepository;
use App\Services\Users\UserService;
use App\Services\Users\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class UserCrudController extends ApiController
{
    public function __construct(
        protected UserRepository $repo,
        protected UserService $service,
        protected RoleRepository $roleRepository,
        protected VerificationService $verificationService,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     security={{"Basic": {}}},
     *     summary="Get users paginated list",
     *     operationId="GetUsersPaginatedList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *          @OA\Schema(type="string", default="status", enum ={"full_name", "email", "status"})
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/OrderType"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by first_name, last_name, email or phone number",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Scope for filter by status", required=false,
     *         @OA\Schema(type="string", default="true", enum={"active", "inactive", "pending"})
     *     ),
     *     @OA\Parameter(name="role_id", in="query", description="Role id", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *
     *     @OA\Response(response=200, description="Paginated data",
     *         @OA\JsonContent(ref="#/components/schemas/UserPaginationResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(UserFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\User\UserReadPermission::KEY);

        $models = $this->repo->allPagination($request->validated());

        return UserPaginationResource::collection($models);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/shortlist",
     *     tags={"Users"},
     *     security={{"Basic": {}}},
     *     summary="Get Users short list",
     *     operationId="GetUsersShortlist",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/ID"),
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for filter by name, email, phone", required=false,
     *          @OA\Schema(type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="roles", in="query", description="Roles id", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(anyOf={@OA\Schema(type="integer")})
     *          )
     *     ),
     *     @OA\Parameter(name="statuses", in="query", description="Statuses user", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(anyOf={@OA\Schema(type="string", enum={"active"})})
     *          )
     *     ),
     *
     *     @OA\Response(response=200, description="User data",
     *         @OA\JsonContent(ref="#/components/schemas/UserShortListResource"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function shortlist(UserShortListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\User\UserShortListReadPermission::KEY);

        return UserShortListResource::collection(
            $this->repo->getAll(
                filters: $request->validated(),
                limit: $request->validated('limit') ?? UserShortListRequest::DEFAULT_LIMIT
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     security={{"Basic": {}}},
     *     summary="Create user",
     *     operationId="CreateUser",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="User data",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(UserRequest $request): UserResource
    {
        $this->authorize(Permission\User\UserCreatePermission::KEY);

        $role = $this->roleRepository->getBy(['id' => $request->getDto()->roleId]);

        $this->authorize(Permission\Role\RolePermissionsGroup::KEY .'.'. $role->name);

        $model = $this->service->create($request->getDto());

        if(!$model->role->isMechanic()){
            $this->verificationService->sendConfirmRegistration($model);
        }

        return UserResource::make($model);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     security={{"Basic": {}}},
     *     summary="Update user",
     *     operationId="UpadteUser",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="User data",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(UserRequest $request, $id): UserResource
    {
        $this->authorize(Permission\User\UserUpdatePermission::KEY);

        /** @var $model User */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.user.not_found")
        );

        $this->authorize(Permission\Role\RolePermissionsGroup::KEY .'.'. $model->role_name);

        return UserResource::make(
            $this->service->update($model, $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     security={{"Basic": {}}},
     *     summary="Get info about user",
     *     operationId="GetInfoAboutUser",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="User data",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): UserResource
    {
        $this->authorize(Permission\User\UserReadPermission::KEY);

        return UserResource::make(
            $this->repo->getBy(['id' => $id], withTrashed: true, withException: true,
                exceptionMessage: __("exceptions.user.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     security={{"Basic": {}}},
     *     summary="Delete user in archive",
     *     operationId="DeleteUserInArchive",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete($id): JsonResponse
    {
        $this->authorize(Permission\User\UserUpdatePermission::KEY);

        if(auth_user()->id == $id){
            throw new \Exception(code: Response::HTTP_FORBIDDEN);
        }

        /** @var $model User */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.user.not_found")
        );

        $this->authorize(Permission\Role\RolePermissionsGroup::KEY .'.'. $model->role_name);

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
