<?php

namespace App\Http\Controllers\V1\Saas\Admins;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\AdminForgotPasswordRequest;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;

class AdminForgotPasswordController extends ApiController
{
    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(AdminForgotPasswordRequest $request): JsonResponse
    {
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response === Password::RESET_LINK_SENT
            ? $this->makeSuccessResponse(null, Response::HTTP_OK)
            : $this->makeErrorResponse(trans($response), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function broker(): PasswordBroker
    {
        return Password::broker('admins');
    }
}

/**
 * @OA\Post(
 *     path="/v1/saas/password-forgot",
 *     tags={"Admins"},
 *     summary="Send link to reset admin password",
 *     operationId="Send link to reset admin password",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter(name="email", in="query", required=true,
 *          @OA\Schema(type="string", default="chernenko.v@wezom.com.ua",)
 *     ),
 *     @OA\Response(response=200, description="Successful operation",),
 * )
 */
