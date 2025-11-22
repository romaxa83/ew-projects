<?php

namespace App\Http\Controllers\V1\Saas;

use App\Http\Controllers\ApiController;
use App\Http\Requests\AuthRequest;
use App\Http\Resources\AuthResource;
use App\Models\Admins\Admin;
use App\Services\Passport\AdminPassportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class AuthController extends ApiController
{

    /**
     * @param AuthRequest $request
     * @param AdminPassportService $authService
     * @return AuthResource|JsonResponse
     * @throws Throwable
     */
    public function login(AuthRequest $request, AdminPassportService $authService)
    {
        $admin = Admin::whereEmail($request->email)->first();

        if (!$admin || !$admin->exists) {
            return $this->makeErrorResponse(trans('auth.empty_login'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$admin->isActive()) {
            return $this->makeErrorResponse(trans('auth.user_deactivated'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $auth = $authService->auth($request->email, $request->password);


        if (isset($auth['error'])) {
            return $this->makeErrorResponse(trans('auth.failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        modelChanged(
            $admin,
            'history.admin_logged_in',
            [
                'full_name' => $admin->full_name,
                'email' => $admin->email,
            ]
        );

        return AuthResource::make($auth);
    }

    public function logout(Request $request, AdminPassportService $authService): JsonResponse
    {
        if ($authService->logout($request->user(Admin::GUARD))) {
            return $this->makeSuccessResponse(trans('auth.logged_out_success'));
        }

        return $this->makeErrorResponse(trans('auth.logout_failed'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param Request $request
     * @param AdminPassportService $authService
     * @return AuthResource|JsonResponse
     * @throws Throwable
     */
    public function refreshToken(Request $request, AdminPassportService $authService)
    {
        $refreshedData = $authService->refreshToken($request->input('refresh_token'));

        if (!is_array($refreshedData)) {
            return $this->makeErrorResponse($refreshedData, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (isset($refreshedData['error'])) {
            return $this->makeErrorResponse($refreshedData['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return AuthResource::make($refreshedData);
    }
}

/**
 * @OA\Post(
 *     path="/v1/saas/login",
 *     tags={"General"},
 *     summary="Login saas admin",
 *     operationId="login",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(name="email", in="query", required=true,
 *          @OA\Schema(type="string", default="superadmin@haulk.com")
 *     ),
 *     @OA\Parameter(name="password", in="query", required=true,
 *          @OA\Schema(type="string", default="admin")
 *     ),
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AuthDataResource")
 *     ),
 * )
 */

/**
 * @OA\Post(path="/v1/saas/logout", tags={"General"}, operationId="logout", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(response=200, description="Successful operation",),
 * )
 */

/**
 * @OA\Post(path="/v1/saas/refresh-token", tags={"General"}, summary="Refresh user token", operationId="refresh-token",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Refresh-token"),
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AuthDataResource")
 *     ),
 * )
 */
