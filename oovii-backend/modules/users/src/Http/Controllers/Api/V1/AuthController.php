<?php

namespace WezomCms\Users\Http\Controllers\Api\V1;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Log;
use Throwable;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Firebase\Events\FcmPush;
use WezomCms\Firebase\Models\Template;
use WezomCms\SmsVerify\Services\SmsVerifyService;
use WezomCms\Users\Dto\UserDto;
use WezomCms\Users\Http\Requests\Api\V1\Auth;
use WezomCms\Users\Models\User;
use WezomCms\Users\Repositories\UserRepository;
use WezomCms\Users\Services\Auth\UserPassportService;
use WezomCms\Users\Services\UserService;

class AuthController extends ApiController
{
    public function __construct(
        protected UserService $userService,
        protected UserRepository $userRepository,
        protected UserPassportService $passportService,
        protected SmsVerifyService $smsVerifyService
    ) {
        parent::__construct();
    }

    /**
     * @OA\Post (
     *     path="/mobile/auth/register",
     *     tags={"Auth"},
     *     summary="Register user",
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/TokensResponse")
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param Auth\RegisterRequest $request
     * @return JsonResponse
     */
    public function register(Auth\RegisterRequest $request): JsonResponse
    {
        try {
            $obj = $this->smsVerifyService->getAndCheckByActionToken($request['actionToken']);
            $obj->delete();

            $dto = UserDto::byRegistry($request->all());
            $user = $this->userService->create($dto);

            $tokens = arrayKeyToCamel(
                $this->passportService->auth(
                    $user->id,
                    $dto->password
                )
            );

            event(new FcmPush($user, Template::TYPE_REGISTRY));

            return self::successJsonMessage($tokens);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::successJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/auth/login",
     *     tags={"Auth"},
     *
     *     summary="Login user",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoginRequest")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/TokensResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param Auth\LoginRequest $request
     * @return JsonResponse
     */
    public function login(Auth\LoginRequest $request): JsonResponse
    {
        try {
            $obj = $this->smsVerifyService->getAndCheckByActionToken($request['actionToken']);
            $obj->delete();

            $phone = $request['phone'];
            /** @var $user User */
            $user = $this->userRepository->getOneBy('phone', $phone, [], true);

            if (!$user) {
                throw new InvalidArgumentException(
                    __(
                        'cms-users::admin.exception.Not found user by phone',
                        [
                            'phone' => $phone
                        ]
                    ),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $dto = UserDto::byLogin($request->all());
            $user = $this->userService->updateByLogin($user, $dto);
            $user->refresh();

            \Auth::login($user);

            $tokens = arrayKeyToCamel(
                $this->passportService->auth(
                    $user->id,
                    config("cms.users.users.password_default")
                )
            );

            return self::successJsonMessage($tokens);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/user/exist-by-phone",
     *     tags={"Auth"},
     *
     *     summary="Check exist user by phone",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CheckByPhoneRequest")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param Auth\CheckByPhoneRequest $request
     * @return JsonResponse
     */
    public function checkByPhone(Auth\CheckByPhoneRequest $request): JsonResponse
    {
        try {
            if ($this->userRepository->existBy('phone', $request['phone'])) {
                return self::successJsonMessage(__('cms-users::admin.message.user exist'));
            }

            throw new Exception(
                __(
                    'cms-users::admin.exception.Not found user by phone',
                    [
                        'phone' => $request['phone']
                    ]
                ), Response::HTTP_NOT_FOUND
            );
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/auth/logout",
     *     tags={"Auth"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Logout user",
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function logout(): JsonResponse
    {
        /** @var $user User */
        $user = \Auth::user();
        try {
            $this->passportService->logout($user);

            return self::successJsonMessage(__("cms-users::admin.message.user logout"));
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/auth/refresh-token",
     *     tags={"Auth"},
     *     summary="Refresh tokens for user",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RefreshTokenRequest")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/TokensResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param Auth\RefreshTokenRequest $request
     * @return JsonResponse
     */
    public function refreshToken(Auth\RefreshTokenRequest $request): JsonResponse
    {
        try {
            $tokens = arrayKeyToCamel(
                $this->passportService->refreshToken(
                    $request["refreshToken"]
                )
            );

            return self::successJsonMessage($tokens);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
