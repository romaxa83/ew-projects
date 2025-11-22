<?php

namespace App\Http\Controllers\Api\Users;

use App\Events\ModelChanged;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Users\ChangePasswordByAdminRequest;
use App\Http\Requests\Users\ChangeStatusUserRequest;
use App\Http\Requests\Users\DestroyUserRequest;
use App\Http\Requests\Users\ReassignDispatcherDriversRequest;
use App\Http\Requests\Users\ReassignDriverOrdersRequest;
use App\Http\Requests\Users\SearchRequest;
use App\Http\Requests\Users\SingleAttachmentRequest;
use App\Http\Requests\Users\UploadPhotoRequest;
use App\Http\Requests\Users\UserFilterRequest;
use App\Http\Requests\Users\UserFuelCardRequest;
use App\Http\Requests\Users\UserHistoryRequest;
use App\Http\Requests\Users\UserRequest;
use App\Http\Resources\History\HistoryListResource;
use App\Http\Resources\History\HistoryPaginatedResource;
use App\Http\Resources\Roles\RoleResource;
use App\Http\Resources\Users\DispatchersListResource;
use App\Http\Resources\Users\DriversListResource;
use App\Http\Resources\Users\DriverVehiclesHistoryResource;
use App\Http\Resources\Users\OwnerVehiclesHistoryResource;
use App\Http\Resources\Users\UserMiniResource;
use App\Http\Resources\Users\UserPaginateResource;
use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Users\DriverShortForCardResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Models\History\UserHistory;
use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use App\Notifications\ChangePasswordByAdminEmail;
use App\Services\Billing\BillingService;
use App\Services\Events\EventService;
use App\Services\Fueling\FuelCardHistoryService;
use App\Services\Histories\UserHistoryService;
use App\Services\Orders\OrderService;
use App\Services\Roles\RoleService;
use App\Services\Users\UserService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Log;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Throwable;

class UserController extends ApiController
{
    use ResetsPasswords;

    protected RoleService $roleService;
    protected FuelCardHistoryService $fuelCardHistoryService;

    protected UserService $userService;

    public function __construct(UserService $userService, RoleService $roleService, FuelCardHistoryService $fuelCardHistoryService)
    {
        parent::__construct();

        $this->userService = $userService->setLoggedUser(authUser());
        $this->fuelCardHistoryService = $fuelCardHistoryService;
        $this->roleService = $roleService;
    }

    /**
     * @param UserFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(UserFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize('users');

        $users = User::query()
            ->with(['roles', 'lastLogin'])
            ->withoutSuperDrivers()
            ->filter($request->validatedForFilter())
            ->sort($request->order_by, $request->order_type)
            ->paginate($request->per_page);

        return UserPaginateResource::collection($users);
    }

    /**
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/users/all-drivers-for-fuel-cards",
     *     tags={"Users"},
     *     summary="Get all drivers for fuel cards",
     *     operationId="Get all drivers for fuel cards",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, email, phone", required=false,
     *           @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriverShortForCardList"),
     *     )
     * )
     */
    public function allDriversForFuelCard(\App\Http\Requests\SearchRequest $request): AnonymousResourceCollection
    {
        $this->authorize('users');
        $users = User::onlyDrivers()
            ->active()
            ->filter($request->validated())
            ->limit(SearchRequest::DEFAULT_LIMIT)
            ->orderByRaw(
                "
                    CASE
                        WHEN owner_id = '" . Auth::id() . "' THEN 1
                        ELSE 0
                    END DESC,
                    concat(first_name, ' ', last_name) ASC
                "
            );

        return DriverShortForCardResource::collection(
            $users->get()
        );
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function roleList(Request $request): AnonymousResourceCollection
    {
        $this->authorize('users');

        return RoleResource::collection(
            $this->roleService->permittedForUser($request->user())
        );
    }

    public function dispatchers(): AnonymousResourceCollection
    {
        return DispatchersListResource::collection(
            User::query()
                ->with(['owned'])
                ->canCreateOrders()
                ->active()
                ->get()
        );
    }

    /**
     * @param UserRequest $request
     * @param UserHistoryService $userHistoryService
     * @param BillingService $billingService
     * @return UserResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/users", tags={"Users"}, summary="Create user", operationId="Create user", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="appliaction/json",
     *              schema=@OA\Schema(ref="#/components/schemas/UserRequest", schema="UserRequestCreate")
     *          ),
     *     ),
     *
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function store(UserRequest $request, UserHistoryService $userHistoryService, BillingService $billingService)
    {
        $role = $this->roleService->findById($request->role_id);
        $this->authorize('roles ' . $role->getAttribute('name'));

        // disable create superadmin
        if ($role->name === User::SUPERADMIN_ROLE) {
            return $this->makeErrorResponse(null, 403);
        }
        // disable create superadmin

        try {
            $user = $this->userService->create($request->getDto(), $role->getAttribute('name'));

            $userHistoryService->track(
                $request->user(),
                $user,
                UserHistory::STATUS_CREATED
            );

            if ($user->isDriver()) {
                $billingService->trackCompanyActiveDrivers($user->getCompany());
            }

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
     *     path="/api/users/{userId}",
     *     tags={"Users"}, summary="Get info about user", operationId="Get user data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function show(User $user): UserResource
    {
        $this->authorize('users read');
        if (
            $user->isDriver()
            && $user->owner_id !== request()->user()->id
            && request()->user()->getRoleName() === User::DISPATCHER_ROLE
        ) {
            $this->makeErrorResponse("This action is unauthorized.", Response::HTTP_FORBIDDEN);
        }

        return UserResource::make($user);
    }

    /**
     * @param UserRequest $request
     * @param User $user
     * @param BillingService $billingService
     * @return UserResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Post(
     *     path="/api/users/{userId}", tags={"Users"},summary="Update user", operationId="Update user", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="User id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="appliaction/json",
     *              schema=@OA\Schema(ref="#/components/schemas/UserRequest", schema="UserRequestUpdate")
     *          ),
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function update(UserRequest $request, User $user, BillingService $billingService)
    {
        $this->authorize('roles ' . $user->getRoleName());

        $role = $this->roleService->findById($request->role_id);
        $this->authorize('roles ' . $role->getAttribute('name'));

        try {
            $user = $this->userService->update($user, $request->getDto(), $role->getAttribute('name'));

            if ($user->isDriver()) {
                $billingService->trackCompanyActiveDrivers($user->getCompany());
            }

            return UserResource::make($user);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param User $user
     * @param DestroyUserRequest $request
     * @param UserHistoryService $userHistoryService
     * @param BillingService $billingService
     * @return JsonResponse
     * @OA\Delete(
     *     path="/api/users/{userId}",
     *     tags={"Users"}, summary="Delete user in archive", operationId="Delete user in archive", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     */
    public function destroy(
        User $user,
        DestroyUserRequest $request,
        UserHistoryService $userHistoryService,
        BillingService $billingService
    ): JsonResponse
    {
        $this->userService->destroy($user);

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param User $user
     * @param ChangeStatusUserRequest $request
     * @param UserHistoryService $userHistoryService
     * @param BillingService $billingService
     * @return UserResource
     * @OA\Put(
     *     path="/api/users/{userId}/change-status",
     *     tags={"Users"}, summary="Change user status", operationId="Change user status", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="User id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function changeStatus(
        User $user,
        ChangeStatusUserRequest  $request,
        UserHistoryService $userHistoryService,
        BillingService $billingService
    ): UserResource
    {
        $user = $this->userService->changeStatus($user);

        $userHistoryService->track(
            $request->user(),
            $user,
            $user->isActive() ? UserHistory::STATUS_ACTIVATED : UserHistory::STATUS_DEACTIVATED
        );

        if ($user->isDriver()) {
            $billingService->trackCompanyActiveDrivers($user->getCompany());
        }

        return UserResource::make($user);
    }

    /**
     * @param UploadPhotoRequest $request
     * @param User $user
     * @return UserResource
     * @throws DiskDoesNotExist|FileDoesNotExist|FileIsTooBig|AuthorizationException
     *
     * @OA\Post(
     *     path="/api/users/{userId}/upload-photo",
     *     tags={"Users"}, summary="Upload user photo", operationId="Upload user photo", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(name="id", in="path", description="User id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="photo", type="string", format="binary",)
     *              )
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function uploadPhoto(UploadPhotoRequest $request, User $user): UserResource
    {
        $this->authorize('users update');
        $this->authorize('roles ' . $user->getRoleName());

        $user->clearImageCollection();
        $user->addImage($request->file($user->getImageField()));

        EventService::users($user)->update()->broadcast();

        return UserResource::make($user);
    }

    /**
     * @param User $user
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/users/{userId}/delete-photo",
     *     tags={"Users"}, summary="Delete photo", operationId="Delete photo", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="User id", required=true,
     *          @OA\Schema(type="integer",default="1",)
     *     ),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     */
    public function deletePhoto(User $user): JsonResponse
    {
        $this->authorize('users update');
        $this->authorize('roles ' . $user->getRoleName());

        $user->clearImageCollection();

        EventService::users($user)->update()->broadcast();

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Get(
     *     path="/api/users/order-creators-list",
     *     tags={"Users"},
     *     summary="Get users who can create orders",
     *     operationId="Get users who can create orders",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserMini")
     *     ),
     * )
     */
    public function orderCreatorsList(): AnonymousResourceCollection
    {
        $users = User::canCreateOrders()
            ->sort('full_name', 'asc');
        return UserMiniResource::collection($users->get());
    }

    /**
     * @OA\Get(
     *     path="/api/users/all-drivers-list",
     *     tags={"Users"},
     *     summary="Get all drivers list",
     *     operationId="Get all drivers",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="all_state", in="query", description="All status by user", required=false,
     *         @OA\Schema(type="boolean", default="false")
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriversListResource")
     *     ),
     * )
     */
    public function allDriversList(Request $request): AnonymousResourceCollection
    {
        $users = User::onlyDrivers()
            ->when(!$request['all_state'], function($q){
                return $q->active();
            })
            ->whereNotNull('owner_id')
            ->orderByRaw(
                "
                    CASE
                        WHEN owner_id = '" . Auth::id() . "' THEN 1
                        ELSE 0
                    END DESC,
                    concat(first_name, ' ', last_name) ASC
                "
            );

        return DriversListResource::collection(
            $users->get()
        );
    }

    /**
     * @param User $user
     * @param ChangePasswordByAdminRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @OA\Post(
     *     path="/api/users/{userId}/change-password",
     *     tags={"Users"}, summary="Change user password", operationId="Change user password", deprecated=false,
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
        $this->authorize('users update');
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
     * Add single user attachment
     *
     * @param SingleAttachmentRequest $request
     * @param User $user
     * @return JsonResponse|UserResource
     *
     * @throws AuthorizationException
     * @OA\Post(
     *     path="/api/users/{userId}/attachments",
     *     tags={"Users"},
     *     summary="Add single attachment to user",
     *     operationId="Add attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="attachment", in="query", description="attachment file", required=false,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function addAttachment(SingleAttachmentRequest $request, User $user)
    {
        $this->authorize('users update');
        $this->authorize('roles ' . $user->getRoleName());

        try {
            return new UserResource(
                $this->userService->addAttachment(
                    $user,
                    $request->attachment
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Delete single user attachment
     *
     * @param User $user
     * @param int $id
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/users/{userId}/attachments/{attachmentId}",
     *     tags={"Users"},
     *     summary="Delete attachment from user",
     *     operationId="Delete attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     */
    public function deleteAttachment(User $user, int $id): JsonResponse
    {
        $this->authorize('users update');
        $this->authorize('roles ' . $user->getRoleName());

        try {
            $this->userService->deleteAttachment($user, $id);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param ReassignDriverOrdersRequest $request
     * @param User $driverFrom
     * @param User $driverTo
     * @return JsonResponse
     * @throws Throwable
     *
     * @OA\Put (
     *     path="users/{driverFrom}/reassign-driver-orders/{driverTo}",
     *     tags={"Users"}, summary="Reassing driver orders to another driver", operationId="Reassing driver orders to another driver", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="driverFrom", in="path", description="First driver ID", required=true, @OA\Schema (type="integer")),
     *     @OA\Parameter (name="driverTo", in="path", description="Second driver ID", required=true, @OA\Schema (type="integer")),
     *
     *     @OA\Response (response=204, description="Successful operation"),
     *     @OA\Response (response=422, description="First driver doesn't have active orders / Validated error"),
     *     @OA\Response (response=500, description="Server error"),
     * )
     */
    public function reassignDriverOrders(ReassignDriverOrdersRequest $request, User $driverFrom, User $driverTo): JsonResponse
    {
        //if driver doesn't change
        if ($driverFrom->id === $driverTo->id) {
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        }
        try {
            resolve(OrderService::class)
                ->setUser($request->user())
                ->reassign(
                    $this->userService->driverActiveOrders($driverFrom),
                    $driverTo,
                    false
                );

        } catch (Exception $exception) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param ReassignDispatcherDriversRequest $request
     * @param User $dispatcherFrom
     * @param User $dispatcherTo
     * @return JsonResponse
     * @throws Throwable
     *
     * @OA\Put (
     *     path="users/{dispatcherFrom}/reassign-dispatcher-drivers/{dispatcherTo}",
     *     tags={"Users"}, summary="Reassigen dispatcher drivers (with orders) to another dispatcher", operationId="Reassigen dispatcher drivers (with orders) to another dispatcher", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="dispatcherFrom", in="path", description="First dispatcher ID", required=true, @OA\Schema (type="integer")),
     *     @OA\Parameter (name="dispatcherTo", in="path", description="Second dispatcher ID", required=true, @OA\Schema (type="integer")),
     *
     *     @OA\Response (response=204, description="Successful operation"),
     *     @OA\Response (response=422, description="Dispatcher doesn't have any drivers/ User isn't dispatcher"),
     *     @OA\Response (response=500, description="Server error"),
     * )
     */
    public function reassignDispatcherDrivers(ReassignDispatcherDriversRequest $request, User $dispatcherFrom, User $dispatcherTo): JsonResponse
    {
        if ($dispatcherFrom->id === $dispatcherTo->id) {
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        }

        try {
            $this->userService->reassignDispatcherDrivers($dispatcherFrom, $dispatcherTo);

            resolve(OrderService::class)
                ->setUser($request->user())
                ->reassign(
                    $this->userService->managerActiveOrders($dispatcherFrom),
                    $dispatcherTo
                );

        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param User $user
     * @return JsonResponse
     * @throws Throwable
     *
     * @OA\Put (
     *     path="users/resend-invitation-link/{userId}",
     *     tags={"Users"}, summary="Resend Invitation Link", operationId="Resend Invitation Link", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response (response=204, description="Successful operation"),
     *     @OA\Response (response=422, description="User is not in Pending status"),
     *     @OA\Response (response=500, description="Server error"),
     * )
     */
    public function resendInvitationLink(User $user): JsonResponse
    {
        $this->authorize('users update');
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
     *     path="/api/users/shortlist",
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

    /**
     * Get driver trucks history
     *
     * @param User $user
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/users/{userId}/driver-trucks-history",
     *     tags={"Users"},
     *     summary="Get driver trucks history",
     *     operationId="Get driver trucks history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriverVehiclesHistoryRawResource")
     *     ),
     * )
     */
    public function driverTrucksHistory(User $user): AnonymousResourceCollection
    {
        $this->authorize('users read');

        return DriverVehiclesHistoryResource::collection(
            $user->driverTrucksHistory()
                ->with(['vehicle', 'vehicle.owner'])
                ->orderBy('id', 'desc')
                ->paginate(7)
        );
    }

    /**
     * Get driver trailers history
     *
     * @param User $user
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/users/{userId}/driver-trailers-history",
     *     tags={"Users"},
     *     summary="Get driver trailers history",
     *     operationId="Get driver trailers history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriverVehiclesHistoryRawResource")
     *     ),
     * )
     */
    public function driverTrailersHistory(User $user): AnonymousResourceCollection
    {
        $this->authorize('trailers read');

        return DriverVehiclesHistoryResource::collection(
            $user->driverTrailersHistory()
                ->with(['vehicle', 'vehicle.owner'])
                ->orderBy('id', 'desc')
                ->paginate(7)
        );
    }

    /**
     * Get owner trucks history
     *
     * @param User $user
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/users/{userId}/owner-trucks-history",
     *     tags={"Users"},
     *     summary="Get owner trucks history",
     *     operationId="Get owner trucks history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OwnerVehiclesHistoryResource")
     *     ),
     * )
     */
    public function ownerTrucksHistory(User $user): AnonymousResourceCollection
    {
        $this->authorize('users read');

        return OwnerVehiclesHistoryResource::collection(
            $user->ownerTrucksHistory()
                ->with(['vehicle', 'vehicle.driver'])
                ->orderBy('id', 'desc')
                ->paginate(7)
        );
    }

    /**
     * Get owner trailers history
     *
     * @param User $user
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/users/{userId}/owner-trailers-history",
     *     tags={"Users"},
     *     summary="Get owner trailers history",
     *     operationId="Get owner trailers history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OwnerVehiclesHistoryResource")
     *     ),
     * )
     */
    public function ownerTrailersHistory(User $user): AnonymousResourceCollection
    {
        $this->authorize('trailers read');

        return OwnerVehiclesHistoryResource::collection(
            $user->ownerTrailersHistory()
                ->with(['vehicle', 'vehicle.driver'])
                ->orderBy('id', 'desc')
                ->paginate(7)
        );
    }

    /**
     * Delete single user attachment
     *
     * @param User $user
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/users/{userId}/delete-mvr-document",
     *     tags={"Users"},
     *     summary="Delete MVR document",
     *     operationId="Delete MVR document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     */
    public function deleteMvrDocument(User $user): JsonResponse
    {
        $this->authorize('users update');
        $this->authorize('roles ' . $user->getRoleName());

        try {
            $this->userService->deleteDriverDocument($user, DriverInfo::ATTACHED_MVR_FILED_NAME);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete medical card document
     *
     * @param User $user
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/users/{userId}/delete-medical-card-document",
     *     tags={"Users"},
     *     summary="Delete medical card document",
     *     operationId="Delete medical card document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     */
    public function deleteMedicalCard(User $user): JsonResponse
    {
        $this->authorize('users update');
        $this->authorize('roles ' . $user->getRoleName());

        try {
            $this->userService->deleteDriverDocument($user, DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete medical card document
     *
     * @param User $user
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/users/{userId}/delete-driver-license-document",
     *     tags={"Users"},
     *     summary="Delete driver license document",
     *     operationId="Delete driver license document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     */
    public function deleteDriverLicenseDocument(User $user): JsonResponse
    {
        $this->authorize('users update');
        $this->authorize('roles ' . $user->getRoleName());

        try {
            $this->userService->deleteDriverLicenseDocument($user, $user->getCurrentDriverLicense());

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete medical card document
     *
     * @param User $user
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/users/{userId}/delete-previous-driver-license-document",
     *     tags={"Users"},
     *     summary="Delete driver license document",
     *     operationId="Delete driver license document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     */
    public function deletePreviousDriverLicenseDocument(User $user): JsonResponse
    {
        $this->authorize('users update');
        $this->authorize('roles ' . $user->getRoleName());

        try {
            $this->userService->deleteDriverLicenseDocument($user, $user->getPreviousDriverLicense());

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user history
     *
     * @param User $user
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/users/{userId}/history",
     *     tags={"Users"},
     *     summary="Get user history",
     *     operationId="Get user history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryListResourceBS")
     *     ),
     * )
     */
    public function history(User $user)
    {
        $this->authorize('users read');

        try {
            return HistoryListResource::collection(
                $this->userService->getHistoryShort($user)
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user history detailed paginate
     *
     * @param User $user
     * @param UserHistoryRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/users/{userId}/history-detailed",
     *     tags={"Users"},
     *     summary="Get user history detailed",
     *     operationId="Get user history detailed",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="dates_range", in="query", description="06/06/2021 - 06/14/2021", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="user_id", in="query", description="user_id", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter( name="page", in="query", description="page", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter( name="per_page", in="query", description="per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryPaginatedResource")
     *     ),
     * )
     */
    public function historyDetailed(User $user, UserHistoryRequest $request)
    {
        $this->authorize('users read');

        try {
            return HistoryPaginatedResource::collection($this->userService->getHistoryDetailed($user, $request));
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @param User $user
     * @param UserFuelCardRequest $request
     * @return UserResource|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/users/{userId}/assigned-fuel-card",
     *     tags={"Users"},
     *     summary="assigned fuel card",
     *     operationId="assigned fuel card",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="userId", in="query", description="userId", required=true,
              @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="fuel_card_id", in="query", description="fuel_card_id", required=true,
     *        @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function assignedFuelCard(User $user, UserFuelCardRequest $request)
    {
        $this->authorize('users update');

        try {
            return new UserResource($this->fuelCardHistoryService->assignedDriver($user, $request->fuel_card_id));
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @param User $user
     * @param UserFuelCardRequest $request
     * @return UserResource|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/users/{userId}/unassigned-fuel-card",
     *     tags={"Users"},
     *     summary="unassigned fuel card",
     *     operationId="unassigned fuel card",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="userId", in="query", description="userId", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="fuel_card_id", in="query", description="fuel_card_id", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function unassignedFuelCard(User $user, UserFuelCardRequest $request)
    {
        $this->authorize('users update');

        try {
            return new UserResource($this->fuelCardHistoryService->unassignedAllDriver($user, $request->fuel_card_id));
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user history users
     *
     * @param User $user
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/users/{userId}/history-users-list",
     *     tags={"Users"},
     *     summary="Get list users changes user",
     *     operationId="Get list users changes user",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserShortList")
     *     ),
     * )
     */
    public function historyUsers(User $user): AnonymousResourceCollection
    {
        $this->authorize('users read');

        return UserShortListResource::collection($this->userService->getHistoryUsers($user));
    }
}

/**
 * @see UserController::index()
 *
 * @OA\Get(
 *     path="/api/users",
 *     tags={"Users"}, summary="Get users paginated list", operationId="Get user data", deprecated=false,
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
 *          @OA\Schema(type="string", default="status", enum ={"id","full_name","status","last_login"})
 *     ),
 *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
 *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
 *     ),
 *     @OA\Parameter(
 *          name="role_id",
 *          in="query", description="Scope for filter by role id, name, email or phone number", required=false,
 *          @OA\Schema(type="integer", default="null",)
 *     ),
 *     @OA\Parameter(
 *          name="name", in="query", description="Scope for filter by name,email or phone number", required=false,
 *          @OA\Schema(type="string", default="null",)
 *     ),
 *     @OA\Parameter(name="status", in="query", description="Scope for filter by status", required=false,
 *          @OA\Schema(type="string", default="true", enum={"active", "inactive", "pending"})
 *     ),
 *     @OA\Parameter(
 *          name="tag_id",
 *          in="query", description="Scope for filter by tag id", required=false,
 *          @OA\Schema(type="integer", default="null",)
 *     ),
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/UserRaw")
 *     ),
 * )
 */

/**
 * @see UserController::roleList()
 *
 * @OA\Get(
 *     path="/api/users/role-list",
 *     tags={"Users"}, summary="Get all roles permitted for user", operationId="Get roles data", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/RolesList")
 *     ),
 * )
 */

/**
 * @see UserController::dispatchers()
 *
 * @OA\Get(
 *     path="/api/users/dispatchers",
 *     tags={"Users"},
 *     summary="Get all active dispatchers for dropdown", operationId="Get dispatchers mini data", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/DispatchersListResource")
 *     ),
 * )
 */
