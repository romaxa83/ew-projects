<?php

namespace App\Http\Controllers\Api\OneC\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\Api\OneC\Auth\LoginResource;
use App\Services\Auth\ModeratorPassportService;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * @group Auth
 */
class AuthController extends Controller
{
    /**
     * Login
     *
     * <aside>
     * <b>access_expires_in</b> and <b>refresh_expires_in</b> values in seconds
     * </aside>
     *
     * @unauthenticated
     *
     * @responseFile 200 docs/api/auth/login.json
     * @responseFile 400 docs/api/auth/invalid.json
     *
     * @throws Throwable
     */
    public function login(LoginRequest $request, ModeratorPassportService $service): LoginResource
    {
        $data = $request->validated();

        return LoginResource::make($service->auth($data['email'], $data['password']));
    }

    /**
     * Refresh token
     *
     * @unauthenticated
     *
     * @responseFile 200 docs/api/auth/login.json
     *
     * @throws Throwable
     */
    public function refreshToken(string $refreshToken, ModeratorPassportService $service): LoginResource
    {
        return LoginResource::make($service->refreshToken($refreshToken));
    }

    /**
     * Logout
     *
     * @response {
     *     "data": true
     * }
     */
    public function logout(ModeratorPassportService $service): JsonResponse
    {
        return response()->json(
            [
                'data' => $service->logout($this->user())
            ]
        );
    }
}
