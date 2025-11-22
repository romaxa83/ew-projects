<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Enums\Users\UserStatus;
use App\Foundations\Modules\Auth\Services\Passport\UserPassportService;
use App\Foundations\Modules\Utils\Tokenizer\Tokenizer;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\TokenRequest;
use App\Http\Requests\Auth\User\LoginRequest;
use App\Http\Resources\Auth\AuthTokenResource;
use App\Http\Resources\Users\ProfileResource;
use App\Models\Users\User;
use App\Repositories\Users\UserRepository;
use App\Services\Users\UserNotificationService;
use App\Services\Users\UserService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends ApiController
{
    public function __construct(
        protected UserPassportService $passportService,
        protected UserRepository $repo,
        protected UserService $service,
        protected UserNotificationService $userNotificationService,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     tags={"Auth"},
     *     summary="Get auth tokens",
     *     operationId="GetAuthTokens",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\RequestBody(required=false,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Auth tokens",
     *         @OA\JsonContent(ref="#/components/schemas/AuthTokenResource")
     *     ),
     *
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function login(LoginRequest $request): JsonResponse|AuthTokenResource
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if(!$user) throw new \Exception(__('auth.invalid_credentials'), Response::HTTP_UNAUTHORIZED);
        if(!$user->isActive()) throw new \Exception(__('exceptions.user.not_found'), Response::HTTP_UNAUTHORIZED);
        if(!Hash::check($request->validated('password'), $user->password))
            throw new \Exception(__('auth.invalid_credentials'), Response::HTTP_UNAUTHORIZED);

        $tokens = $this->passportService->auth(
            $request->validated('email'),
            $request->validated('password')
        );

        return AuthTokenResource::make($tokens);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/refresh-token",
     *     tags={"Auth"},
     *     summary="Refresh user token",
     *     operationId="RefreshToken",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\RequestBody(required=false,
     *         @OA\JsonContent(ref="#/components/schemas/RefreshTokenRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Auth tokens",
     *         @OA\JsonContent(ref="#/components/schemas/AuthTokenResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse|AuthTokenResource
    {
        try {
            $refreshedTokens = $this->passportService
                ->refreshToken($request->validated('refresh_token'));

            if (gettype($refreshedTokens) !== 'array') {
                throw new \Exception(' ', Response::HTTP_BAD_REQUEST);
            }

            if (isset($refreshedTokens['error'])) {
                throw new \Exception($refreshedTokens['message'], Response::HTTP_BAD_REQUEST);
            }

            return AuthTokenResource::make($refreshedTokens);
        } catch (\Throwable $e){
            throw new \Exception($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     tags={"Auth"},
     *     security={{"Basic": {}}},
     *     summary="User logout",
     *     operationId="UserLogout",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Message",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function logout(): JsonResponse|AuthTokenResource
    {
        if(!$this->passportService->logout(Auth::user())){
            throw new \Exception(__('auth.logout_failed'), Response::HTTP_BAD_REQUEST);
        }

        return $this->successJsonMessage(__('auth.logged_out_success'));
    }

    /**
     * @OA\Post (
     *     path="/api/v1/password-forgot",
     *     tags={"Auth"},
     *     summary="User forgot password",
     *     operationId="UserForgotPassword",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\RequestBody(required=false,
     *         @OA\JsonContent(ref="#/components/schemas/ForgotPasswordRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Simple message",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            /** @var $model User */
            $model = $this->repo->getBy(['email' => $request->validated('email')]);

            $this->userNotificationService->forgotPassword($model);

            return $this->successJsonMessage(
                __('messages.forgot_password.send.success', ['email' => $request->validated('email')])
            );
        } catch (\Throwable $e){
            return $this->errorJsonMessage($e);
        }
    }

    /**
     * @OA\Post (
     *     path="/api/v1/reset-password",
     *     tags={"Auth"},
     *     summary="User set new password",
     *     operationId="UserResetPassword",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\RequestBody(required=false,
     *         @OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Simple messagee",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="403", description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse|ProfileResource
    {
        $args = $request->validated();

        try {
            make_transaction(function () use ($args) {

                $decrypt = Tokenizer::decryptToken($args['token']);

                /** @var $model User */
                $model = $decrypt->modelClass::find($decrypt->modelId);

                Tokenizer::assertToken($decrypt, $model);

                $model->setPassword($args['password']);
                $model->email_verified_at = CarbonImmutable::now();
                if($model->status->isPending()){
                    $model = $this->service->setStatus($model, UserStatus::ACTIVE());
                }
                $model->save();

                $this->userNotificationService->resetPassword($model, $args['password']);
            });

            return $this->successJsonMessage(
                __('messages.reset_password.success')
            );
        } catch (\Throwable $e){
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/v1/check-password-token",
     *     tags={"Auth"},
     *     summary="Check passord token",
     *     operationId="CheckPasswordToken",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\RequestBody(required=false,
     *         @OA\JsonContent(ref="#/components/schemas/TokenRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Simple messagee",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function checkPasswordToken(TokenRequest $request): JsonResponse|ProfileResource
    {
        $token = $request->validated('token');

        if(Tokenizer::checkToken($token, config('token.reset_password.live'))) {
            return $this->successJsonMessage(__('messages.token.valid'));
        }

        return $this->errorJsonMessage(__('messages.token.not_valid'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
