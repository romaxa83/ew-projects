<?php

namespace App\Http\Controllers\Api;

use App\Events\ModelChanged;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Users\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Laravel\Passport\Token;

class ResetPasswordController extends ApiController
{
    use ResetsPasswords;

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );
        return $response === Password::PASSWORD_RESET
            ? $this->makeSuccessResponse(null, Response::HTTP_OK)
            : $this->makeErrorResponse(trans($response), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function resetPassword(User $user, $password): void
    {
        $user->updatePassword($password);
        $user->tokens->each(fn(Token $token) => $token->revoke());
        if ($user->isPending()) {
            $user->toggleStatus();
        }

        event(new ModelChanged($user, 'Password changed'));
    }
}

/**
 * @OA\Post(
 *     path="/api/password-set",
 *     tags={"General"},
 *     summary="Set password",
 *     operationId="Set or reset password",
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
