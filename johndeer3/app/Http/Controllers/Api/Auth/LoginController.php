<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Auth\LoginRequest;
use App\Http\Request\Auth\RefreshTokenRequest;
use App\Http\Request\User\RequestResetPassword;
use App\Jobs\MailSendJob;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Resources\User\UserResource;
use App\Services\Auth\UserPassportService;
use App\Services\Telegram\TelegramDev;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class LoginController extends ApiController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UserService $userService,
        protected UserPassportService $passportService
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Post (
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Авторизация пользователя",
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/BearerTokens")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function login(LoginRequest $request)
    {
        $login = $request->input('login');
        $password = $request->input('password');
        $fcmToken = $request->input('fcm_token');
        /** @var  $user User */
        $user = $this->userRepository->getBy('login', $login);

        if(!$user){
            return $this->errorJsonMessage(__('message.user_wrong_login'));
        }

        if(!$user->isActive()){
            return $this->errorJsonMessage(__('message.no_access'), 403);
        }

        if(!password_verify($password, $user->password)){
            return $this->errorJsonMessage(__('message.user_wrong_password'));
        }

        if($fcmToken){
            $this->userService->setFcmToken($user, $fcmToken);
        }

        $auth = $this->passportService->auth($user->email, $password);
        if (isset($auth['error'])) {
            return $this->errorJsonMessage(__('message.token_false'));
        }

        Auth::login($user);

        $auth['isAdmin'] = false;
        if($user->isAdmin()){
            $auth['isAdmin'] = true;
        }

        TelegramDev::info("Авторизовался пользователь", $user->login . "[{$user->id}]");

        return $this->successJsonMessage($auth);
    }

    /**
     * @OA\Post (
     *     path="/api/refresh-token",
     *     tags={"Auth"},
     *     summary="Обновление токена",
     *     description="Обновление токена, когда истекает время жизни access_token",
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/RefreshTokenRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/BearerTokens")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function refreshToken(RefreshTokenRequest $request)
    {
        $refreshToken = $this->passportService->refreshToken($request->input('refresh_token'));

        if (!is_array($refreshToken)) {
            return $this->errorJsonMessage($refreshToken);
        }
        if (isset($refreshToken['error'])) {
            return $this->errorJsonMessage($refreshToken['error_description']);
        }
        return $this->successJsonMessage($refreshToken);
    }

    /**
     * @OA\Get (
     *     path="/api/me",
     *     tags={"Auth", "User (for admin)"},
     *     summary="Получение авторизованого пользователя",
     *     security={{"Basic": {}}},
     *
     *     @OA\Response(response="200", description="Пользователь",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function me()
    {
        return $this->successJsonMessage(
            UserResource::make(Auth::user())
        );
    }

    /**
     * @OA\Get (
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Выход",
     *     security={{"Basic": {}}},
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessMessageResponse")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function logout()
    {
        /** @var User $user */
        $user = Auth::user();

        $token = $user->token();

        if (!($token && $token->revoke())) {
            return $this->errorJsonMessage(__('message.errors.not revoke token'));
        }

        return $this->successJsonMessage(__('message.user_logout'));
    }

    /**
     * @OA\Post (
     *     path="/api/reset-password",
     *     tags={"Auth", "User (for admin)"},
     *     summary="Cбросить пароль",
     *     description="Зброс пароля для пользователя, который будет выслан на почту",
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/RequestResetPassword")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessMessageResponse")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function resetPassword(RequestResetPassword $request)
    {

        /** @var User $user */
        $user = $this->userRepository->getBy('email', $request['email']);
        \App::setLocale($user->lang);
        try {
            $password = User::generateRandomPassword();

            $user = $this->userService->changePassword($user, $password);
            $user = $this->userService->addIosLink($user);

            MailSendJob::dispatch([
                'type' => 'reset-password',
                'password' => $password,
                'user' => $user
            ]);

            return $this->successJsonMessage(__('message.reset_password_success'));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage(), $error->getCode());
        }
    }
}
