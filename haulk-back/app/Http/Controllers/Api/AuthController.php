<?php

namespace App\Http\Controllers\Api;

use App\Events\ModelChanged;
use App\Http\Controllers\ApiController;
use App\Http\Requests\AuthRequest;
use App\Http\Resources\AuthResource;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Services\Passport\UserPassportService;
use Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class AuthController extends ApiController
{
    /**
     * @param AuthRequest $request
     * @param UserPassportService $authService
     * @return AuthResource|JsonResponse
     * @throws Throwable
     */
    public function login(AuthRequest $request, UserPassportService $authService)
    {

        $user = User::whereEmail($request->email)->first();

        if (!$user || !$user->exists) {
            return $this->makeErrorResponse(trans('auth.empty_login'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$user->isActive()) {
            return $this->makeErrorResponse(trans('auth.user_deactivated'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ((!$user->getCompany() || !$user->getCompany()->isActive()) && !$user->isBodyShopUser()) {
            return $this->makeErrorResponse(trans('auth.company_not_active'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (Config::get('mobile') === false && $user->isDriver()) {
            return $this->makeErrorResponse(trans('auth.failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $auth = $authService->auth($request->email, $request->password);

        if (isset($auth['error'])) {
            return $this->makeErrorResponse(trans('auth.failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        event(
            new ModelChanged(
                $user,
                'history.user_logged_in',
                [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                ]
            )
        );

        return AuthResource::make($auth);
    }

    public function logout(Request $request, UserPassportService $authService): JsonResponse
    {
        if ($authService->logout($request->user())) {
            return $this->makeSuccessResponse(trans('auth.logged_out_success'));
        }

        return $this->makeErrorResponse(trans('auth.logout_failed'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param Request $request
     * @param UserPassportService $authService
     * @return AuthResource|JsonResponse
     * @throws Throwable
     */
    public function refreshToken(Request $request, UserPassportService $authService)
    {
        $refreshedData = $authService->refreshToken($request->input('refresh_token'));

        if (gettype($refreshedData) !== 'array') {
            return $this->makeErrorResponse($refreshedData, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (isset($refreshedData['error'])) {
            return $this->makeErrorResponse($refreshedData['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return AuthResource::make($refreshedData);
    }
}

/**
 * @see AuthController::login()
 * @OA\Post(
 *     path="/api/login",
 *     tags={"General"},
 *     summary="Login user",
 *     operationId="login",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(
 *          name="email",
 *          in="query",
 *          description="User email",
 *          required=true,
 *          @OA\Schema(
 *              type="string",
 *               default="superadmin@haulk.com"
 *          )
 *     ),
 *     @OA\Parameter(
 *          name="password",
 *          in="query",
 *          description="User password",
 *          required=true,
 *          @OA\Schema(
 *              type="string",
 *              default="admin"
 *          )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AuthDataResource")
 *     ),
 * )
 */

/**
 * @see AuthController::logout()
 *
 * @OA\Post(
 *     path="/api/logout",
 *     tags={"General"},
 *     summary="Logout user from system",
 *     operationId="logout",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *     ),
 * )
 */

/**
 * @see AuthController::refreshToken()
 * @OA\Post(
 *     path="/api/refresh-token",
 *     tags={"General"},
 *     summary="Refresh user token",
 *     operationId="refresh-token",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Refresh-token"),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AuthDataResource")
 *     ),
 * )
 */
