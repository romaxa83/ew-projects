<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends ApiController
{
    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse
    {
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response === Password::RESET_LINK_SENT
            ? $this->makeSuccessResponse(null, Response::HTTP_OK)
            : $this->makeErrorResponse(trans($response), Response::HTTP_UNPROCESSABLE_ENTITY);
    }


}

/**
 * @OA\Post(
 *     path="/api/password-forgot",
 *     tags={"General"},
 *     summary="Send link to reset password",
 *     operationId="Send link to reset password",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Parameter(name="email",
 *          in="query",
 *          description="User email",
 *          required=true,
 *          @OA\Schema(type="string", default="chernenko.v@wezom.com.ua",)
 *     ),
 *     @OA\Response(response=200, description="Successful operation",),
 * )
 */
