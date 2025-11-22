<?php

namespace App\Http\Controllers\V1\Saas\Admins;

use App\Events\ModelChanged;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\AdminResetPasswordRequest;
use App\Models\Admins\Admin;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Laravel\Passport\Token;

class ResetPasswordController extends ApiController
{
    use ResetsPasswords;

    public function reset(AdminResetPasswordRequest $request): JsonResponse
    {
        $response = $this->broker()->reset(
            $this->credentials($request),
            fn(Admin $user, $password) => $this->resetPassword($user, $password)
        );

        return $response === Password::PASSWORD_RESET
            ? $this->makeSuccessResponse(null, Response::HTTP_OK)
            : $this->makeErrorResponse(trans($response), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function broker(): PasswordBroker
    {
        return Password::broker('admins');
    }

    protected function resetPassword(Admin $user, $password): void
    {
        $user->updatePassword($password);
        $user->tokens->each(fn(Token $token) => $token->revoke());

        event(new ModelChanged($user, 'Password changed'));
    }

}

/**
 * @OA\Post(
 *     path="/v1/saas/password-set",
 *     tags={"Admins"},
 *     summary="Set password",
 *     operationId="Set or reset admin password",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter(name="email", in="query", required=true,
 *          @OA\Schema(type="string", default="chernenko.v@wezom.com.ua")
 *     ),
 *     @OA\Parameter(name="token", in="query", required=true,
 *          @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(name="password", in="query", required=true,
 *          @OA\Schema(type="string", default="123456")
 *     ),
 *     @OA\Parameter(name="password_confirmation", in="query", required=true,
 *          @OA\Schema(type="string", default="123456")
 *     ),
 *     @OA\Response(response=200, description="Successful operation",
 *     ),
 * )
 */
