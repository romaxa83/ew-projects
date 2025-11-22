<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Events\Events\Users\UserChangedEvent;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\Users\User;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Repositories\Users\UserRepository;
use App\Services\Users\UserService;
use App\Services\Users\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserActionController extends ApiController
{
    public function __construct(
        protected UserRepository $repo,
        protected UserService $service,
        protected VerificationService $verificationService
    )
    {}

    /**
     * @OA\Put(
     *     path="/api/v1/users/resend-invitation-link/{id}",
     *     tags={"Users"},
     *     security={{"Basic": {}}},
     *     summary="Resend Invitation Link",
     *     operationId="ResendInvitationLink",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function resendInvitationLink($id): JsonResponse
    {
        $this->authorize(Permission\User\UserUpdatePermission::KEY);

        if(auth_user()->role->isAdmin()){
            throw new \Exception(__('This action is unauthorized.'), 403);
        }

        /** @var $model User */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.user.not_found")
        );

        $this->authorize(Permission\Role\RolePermissionsGroup::KEY .'.'. $model->role_name);

        if (!$model->status->isPending()) {
            return $this->errorJsonMessage(__('exceptions.user.not_pending_status'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->verificationService->sendConfirmRegistration($model);

        return $this->successJsonMessage();
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}/change-status",
     *     tags={"Users"},
     *     security={{"Basic": {}}},
     *     summary="Change user status",
     *     operationId="ChangeUserStatus",
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
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function changeStatus($id): UserResource
    {
        $this->authorize(Permission\User\UserUpdatePermission::KEY);

        /** @var $model User */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.user.not_found")
        );

        $this->authorize(Permission\Role\RolePermissionsGroup::KEY .'.'. $model->role_name);

        return UserResource::make($this->service->changeStatus($model));
    }

    /**
     * @OA\Put(
     *     path="/api/id/users/{id}/change-password",
     *     tags={"Users"},
     *     security={{"Basic": {}}},
     *     summary="Change user password",
     *     operationId="ChangeUserPassword",
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
     *         @OA\JsonContent(ref="#/components/schemas/ChangePasswordRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function changePassword(ChangePasswordRequest $request, $id): JsonResponse
    {
        $this->authorize(Permission\User\UserUpdatePermission::KEY);

        /** @var $model User */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.user.not_found")
        );

        $this->authorize(Permission\Role\RolePermissionsGroup::KEY .'.'. $model->role_name);

        $model->setPassword($request->validated('password'), true);

        event(new UserChangedEvent($model));

        return $this->successJsonMessage(__('messages.user.change_password'));
    }
}

