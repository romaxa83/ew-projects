<?php

namespace App\Http\Controllers\Api\BodyShop\Users;

use App\Events\ModelChanged;
use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Users\SearchRequest;
use App\Http\Requests\BodyShop\Users\UserRequest;
use App\Http\Requests\BodyShop\Users\ChangePasswordByAdminRequest;
use App\Http\Requests\BodyShop\Users\ChangeStatusUserRequest;
use App\Http\Requests\BodyShop\Users\DestroyUserRequest;
use App\Http\Requests\BodyShop\Users\UserFilterRequest;
use App\Http\Resources\BodyShop\Users\UserPaginateResource;
use App\Http\Resources\BodyShop\Users\UserResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Models\Users\User;
use App\Notifications\ChangePasswordByAdminEmail;
use App\Services\Histories\UserHistoryService;
use App\Services\Roles\RoleService;
use App\Services\Users\UserService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Log;
use Throwable;

class UserController extends ApiController
{
    use ResetsPasswords;

    protected UserService $userService;

    protected RoleService $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        parent::__construct();

        $this->userService = $userService->setLoggedUser(authUser());
        $this->roleService = $roleService;
    }

    /**
     * @param UserFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/body-shop/users",
     *     tags={"Users Body Shop"}, summary="Get users paginated list", operationId="Get user data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Users per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *          @OA\Schema(type="string", default="status", enum ={"full_name", "email", "status"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Parameter(
     *          name="name", in="query", description="Scope for filter by first_name, last_name, email or phone number", required=false,
     *          @OA\Schema(type="string", default="null",)
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Scope for filter by status", required=false,
     *          @OA\Schema(type="string", default="true", enum={"active", "inactive", "pending"})
     *     ),
     *     @OA\Parameter(name="role_id", in="query", description="Role id", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BSUserRaw")
     *     ),
     * )
     *
     * @todo moved
     */
    public function index(UserFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize('bs-users');

        $users = User::query()
            ->with(['lastLogin'])
            ->onlyBodyShopUsers()
            ->withoutBSSuperAdmin()
            ->filter($request->validated())
            ->sort($request->order_by, $request->order_type)
            ->paginate($request->per_page);

        return UserPaginateResource::collection($users);
    }

    /**
     * @param UserRequest $request
     * @param UserHistoryService $userHistoryService
     * @return UserResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/users", tags={"Users Body Shop"}, summary="Create body shop admin", operationId="Create body shop admin", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="role_id", in="query", description="User role id", required=true,
     *          @OA\Schema(type="int", default="1",)
     *     ),
     *     @OA\Parameter(name="first_name", in="query", description="User first name", required=true,
     *          @OA\Schema(type="string", default="Vlad",)
     *     ),
     *     @OA\Parameter(name="last_name", in="query", description="User last name", required=true,
     *          @OA\Schema(type="string", default="Chernenko",)
     *     ),
     *     @OA\Parameter(name="email", in="query", description="User email", required=true,
     *          @OA\Schema(type="string", default="chernenko.v@wezom.com.ua",)
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="User phone", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phone_extension", in="query", description="Phone extension", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phones", in="query", description="Additional phone", required=false,
     *          @OA\Schema(type="array", description="User aditional phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          )
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BSUser")
     *     ),
     * )
     */
    public function store(UserRequest $request)
    {
        $this->authorize('bs-users create');

        $role = $this->roleService->findById($request->role_id);
        $this->authorize('roles ' . $role->getAttribute('name'));

        try {
            $user = $this->userService->create($request->getDto(), $role->getAttribute('name'));

            return UserResource::make($user);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param User $user
     * @return UserResource
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/body-shop/users/{userId}",
     *     tags={"Users Body Shop"}, summary="Get info about user", operationId="Get user data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BSUser")
     *     ),
     * )
     * @todo moved
     */
    public function show(User $user): UserResource
    {
        $this->authorize('bs-users read');

        return UserResource::make($user);
    }

    /**
     * @param UserRequest $request
     * @param User $user
     * @return UserResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Post(
     *     path="/api/body-shop/users/{userId}", tags={"Users Body Shop"},summary="Update user", operationId="Update user", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="User id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="role_id", in="query", description="User role id", required=true,
     *          @OA\Schema(type="int", default="1",)
     *     ),
     *     @OA\Parameter(name="first_name", in="query", description="User first name", required=true,
     *          @OA\Schema(type="string", default="Vlad",)
     *     ),
     *     @OA\Parameter(name="last_name", in="query", description="User last name", required=true,
     *          @OA\Schema(type="string", default="Chernenko",)
     *     ),
     *     @OA\Parameter(name="email", in="query", description="User email", required=true,
     *          @OA\Schema(type="string", default="chernenko.v@wezom.com.ua",)
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="User phone", required=false,
     *          @OA\Schema(type="string",default="1234567",)
     *     ),
     *     @OA\Parameter(name="phone_extension", in="query", description="Phone extension", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phones", in="query", description="Additional phone", required=false,
     *          @OA\Schema(type="array", description="User aditional phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BSUser")
     *     ),
     * )
     */
    public function update(UserRequest $request, User $user)
    {
        $this->authorize('bs-users update');
        $this->authorize('roles ' . $user->getRoleName());

        $role = $this->roleService->findById($request->role_id);
        $this->authorize('roles ' . $role->getAttribute('name'));

        try {
            $user = $this->userService->update($user, $request->getDto(), $role->getAttribute('name'));

            return UserResource::make($user);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param User $user
     * @param DestroyUserRequest $request
     * @return JsonResponse
     * @OA\Delete(
     *     path="/api/body-shop/users/{userId}",
     *     tags={"Users Body Shop"}, summary="Delete user in archive", operationId="Delete user in archive", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     */
    public function destroy(User $user, DestroyUserRequest $request): JsonResponse
    {
        $this->authorize('roles ' . $user->getRoleName());

        $this->userService->destroy($user);

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param User $user
     * @param ChangeStatusUserRequest $request
     * @param UserHistoryService $userHistoryService
     * @return UserResource
     * @OA\Put(
     *     path="/api/body-shop/users/{userId}/change-status",
     *     tags={"Users Body Shop"}, summary="Change user status", operationId="Change user status", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="User id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BSUser")
     *     ),
     * )
     */
    public function changeStatus(
        User $user,
        ChangeStatusUserRequest  $request
    ): UserResource
    {
        $user = $this->userService->changeStatus($user);

        return UserResource::make($user);
    }

    /**
     * @param User $user
     * @param ChangePasswordByAdminRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @OA\Post(
     *     path="/api/body-shop/users/{userId}/change-password",
     *     tags={"Users Body Shop"}, summary="Change user password", operationId="Change user password", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="User id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="password", in="query", description="", required=true,
     *          @OA\Schema(type="string", default="")
     *     ),
     *     @OA\Parameter(name="password_confirmation", in="query", description="", required=true,
     *          @OA\Schema(type="string", default="")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     */
    public function changePassword(User $user, ChangePasswordByAdminRequest $request): JsonResponse
    {
        $this->authorize('bs-users update');
        $this->authorize('roles ' . $user->getRoleName());

        if ($user->updatePassword($request->input('password'))) {
            Notification::route('mail', $user->email)->notify(new ChangePasswordByAdminEmail($user));

            event(
                new ModelChanged(
                    $user, 'history.password_changed_by_admin', [
                             'admin_full_name' => $request->user()->full_name,
                             'admin_email' => $request->user()->email,
                             'user_full_name' => $user->full_name,
                             'user_email' => $user->email,
                         ]
                )
            );

            return $this->makeSuccessResponse(trans('Password changed.'), 200);
        }

        return $this->makeErrorResponse(null, 500);
    }

    /**
     * @param User $user
     * @return JsonResponse
     * @throws Throwable
     *
     * @OA\Put (
     *     path="api/body-shop/users/resend-invitation-link/{userId}",
     *     tags={"Users Body Shop"}, summary="Resend Invitation Link", operationId="Resend Invitation Link", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response (response=204, description="Successful operation"),
     *     @OA\Response (response=422, description="User is not in Pending status"),
     *     @OA\Response (response=500, description="Server error"),
     * )
     *
     * @todo moved
     */
    public function resendInvitationLink(User $user): JsonResponse
    {
        $this->authorize('bs-users update');
        $this->authorize('roles ' . $user->getRoleName());

        if (!$user->isPending()) {
            return $this->makeErrorResponse(trans('User is not in Pending status'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->userService->resendInvitationLink($user);
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param SearchRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/users/shortlist",
     *     tags={"Users"},
     *     summary="Get Users short list",
     *     operationId="Get Users data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, email, phone", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="searchid", in="query", description="Filter by id", required=false,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="roles", in="query", description="Roles id", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(anyOf={@OA\Schema(type="integer")})
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserShortList"),
     *     )
     * )
     *
     * @todo moved
     * @return JsonResponse
     */
    public function shortlist(SearchRequest $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->filter($request->validated())
            ->limit(SearchRequest::DEFAULT_LIMIT)
            ->get();

        return UserShortListResource::collection($users);
    }
}
