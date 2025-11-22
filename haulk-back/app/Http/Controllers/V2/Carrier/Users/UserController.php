<?php


namespace App\Http\Controllers\V2\Carrier\Users;


use App\Http\Requests\V2\Users\UserRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\History\UserHistory;
use App\Models\Users\User;
use App\Services\Billing\BillingService;
use App\Services\Histories\UserHistoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserController extends \App\Http\Controllers\V1\Carrier\Users\UserController
{
    /**
     * @param UserRequest $request
     * @param UserHistoryService $userHistoryService
     * @param BillingService $billingService
     * @return UserResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/v2/carrier/users", tags={"Users V2"}, summary="Create user", operationId="Create user", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="appliaction/json",
     *              schema=@OA\Schema(ref="#/components/schemas/UserRequest", schema="UserRequestCreateV2")
     *          ),
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function storeV2(
        UserRequest $request,
        UserHistoryService $userHistoryService,
        BillingService $billingService
    )
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
                UserHistory::STATUS_ACTIVATED // UserHistory::STATUS_CREATED
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
     * @param UserRequest $request
     * @param User $user
     * @param BillingService $billingService
     * @return UserResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Post(
     *     path="/v2/carrier/users/{userId}", tags={"Users V2"},summary="Update user", operationId="Update user", deprecated=false,
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
     *              schema=@OA\Schema(ref="#/components/schemas/UserRequest", schema="UserRequestUpdateV2")
     *          ),
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function updateV2(UserRequest $request, User $user, BillingService $billingService)
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
}
