<?php

namespace WezomCms\Users\Http\Controllers\Api\V1;

use App\Exceptions\SmsTokenExpiredException;
use App\Exceptions\SmsTokenIncorrectException;
use App\Exceptions\UserNotFoundException;
use WezomCms\Core\Api\ErrorCode;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\Converts\AuthTokenConvert;
use WezomCms\Users\Http\Requests\Api\Auth\PhoneRequest;
use WezomCms\Users\Http\Requests\Api\Auth\RefreshTokenRequest;
use WezomCms\Users\Http\Requests\Api\Auth\RegisterRequest;
use WezomCms\Users\Http\Requests\Api\Auth\SmsVerifyRequest;
use WezomCms\Users\Models\User;
use WezomCms\Users\Repositories\OAuthRepository;
use WezomCms\Users\Repositories\UserRepository;
use WezomCms\Users\Services\Auth\UserPassportService;
use WezomCms\Users\Services\OAuthService;
use WezomCms\Users\Services\PhoneVerifyService;
use WezomCms\Users\Services\UserCarService;
use WezomCms\Users\Services\UserService;

class AuthController extends ApiController
{
    private UserService $userService;
    private OAuthService $authService;
    private UserRepository $userRepository;
    private PhoneVerifyService $phoneVerifyService;
    private UserCarService $userCarService;
    private OAuthRepository $authRepository;
    /**
     * @var UserPassportService
     */
    private UserPassportService $passportService;

    public function __construct(
        UserService $userService,
        OAuthService $authService,
        UserRepository $userRepository,
        PhoneVerifyService $phoneVerifyService,
        UserCarService $userCarService,
        OAuthRepository $authRepository,
        UserPassportService $passportService
    )
    {
        parent::__construct();

        $this->userService = $userService;
        $this->authService = $authService;
        $this->userRepository = $userRepository;
        $this->phoneVerifyService = $phoneVerifyService;
        $this->userCarService = $userCarService;
        $this->authRepository = $authRepository;
        $this->passportService = $passportService;
    }

    /**
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function register(RegisterRequest $request)
    {
        try {
            Telegram::event('запрос на регистрацию пользователя');

//            if($this->userRepository->getByPhone($request['phone'])){
//                throw new \Exception(__('cms-users::site.message.user phone exist'));
//            }

            $user = $this->userService->create($request->all());

            Telegram::event('пользователь записан в бд ('. $user->id .'), но не верефицирован в 1с');

            if(isset($request['vehicles']) && !empty($request['vehicles'])){
                Telegram::event('при регистариции внесены данные по машине');

                $this->userCarService->addCars($request['vehicles'], $user);

                Telegram::event('машины ('. count($request['vehicles']) .') прикреплены к пользователю');
            }

            $message = __('cms-users::site.message.register_but_not_verify');

            $res = [
                'message' => $this->phoneVerifyService->requestPhoneVerify($user, $message),
                'userId' => $user->id
            ];

            return $this->successJsonMessage($res);

        } catch(\Exception $exception){
            Telegram::event('при регистрации произошла ошибка');
            Telegram::event($exception->getMessage());

            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @param PhoneRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function smsRequest(PhoneRequest $request)
    {
        try {

            $user = $this->userRepository->getByPhone($request->input('phone'));
            Telegram::event('запрос на sms-код для телефона ('. $request->input('phone') .')');

            return $this->successJsonMessage($this->phoneVerifyService->requestPhoneVerify($user));

        } catch(UserNotFoundException $exception){
            return $this->errorJsonMessage($exception->getMessage(),ErrorCode::USER_NOT_FOUND);
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     *  - если есть активный сеанс, а dropCurrentSession=false - получаем ошибку с кодом 1
     *  - если есть активный сеанс, а dropCurrentSession=true - затираем активные токены, делаем новые
     *
     * @param SmsVerifyRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function smsVerify(SmsVerifyRequest $request)
    {
        try {
            $user = $this->userRepository->getByPhone($request->input('phone'));

            // проверяем есть ли deviceId
            if($user->device_id == null){
               $user = $this->userService->setDeviceId($user, $request->input('deviceId'));
            } else {
                // если есть deviceId и не совпадает с текущим
                if(!$user->checkDeviceId($request->input('deviceId'))){
                    $record = $this->authRepository->authUserRow($user->id);
                    // если есть активный сеанс, а dropCurrentSession=false - кидаем ошибку с кодом 1
                    if($record && !filter_var($request->input('dropCurrentSession'),FILTER_VALIDATE_BOOLEAN)){
                        return $this->errorJsonMessage(
                            __('cms-users::site.message.device id wrong'), ErrorCode::HAS_ACTIVE_SESSION
                        );
                    }
                    // если есть активный сеанс, а dropCurrentSession=true - затираем активные токены, делаем новые
                    if($record && filter_var($request->input('dropCurrentSession'),FILTER_VALIDATE_BOOLEAN)){

                        $this->authRepository->deleteRefreshTokenByAuthId($record->id);
                        $this->authRepository->deleteAuthUserRow($user->id);
                        // перезаписываем новый deviceId
                        $user = $this->userService->setDeviceId($user, $request->input('deviceId'));
                    }
                }
            }

            Telegram::event('запрос на верификацию по телефону ('. $request->input('phone') .') с кодом ('. $request->input('code') .')');

            if($this->phoneVerifyService->phoneVerify($user, $request->input('code'))){

                \Auth::login($user);

//                $tokens = $this->passportService->auth($user->id, User::DEFAULT_PASSWORD);
                $tokens = $this->authService->getBearerToken($user->id);

                return $this->successJsonMessage(
                    AuthTokenConvert::toFront($tokens)
                );
            }

            return $this->errorJsonMessage(__('cms-users::site.message.something wrong'));

        } catch (SmsTokenIncorrectException $exception){
            return $this->errorJsonMessage($exception->getMessage(),ErrorCode::SMS_TOKEN_INCORRECT);
        } catch (SmsTokenExpiredException $exception){
            return $this->errorJsonMessage($exception->getMessage(),ErrorCode::SMS_TOKEN_EXPIRED);
        } catch(UserNotFoundException $exception){
            return $this->errorJsonMessage($exception->getMessage(),ErrorCode::USER_NOT_FOUND);
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

//    public function refreshToken(RefreshTokenRequest $request)
//    {
//        try {
//            $data = $this->passportService->refreshToken($request['refreshToken']);
//
//            return $this->successJsonMessage(
//                $data
//            );
//        } catch(\Exception $exception){
//            return $this->errorJsonMessage($exception->getMessage());
//        }
//    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        try {
            $data = $this->authService->getRefreshToken($request->input('refreshToken'));

            if(isset($data['error'])){
                throw new \Exception($data['error_description']);
            }

            return $this->successJsonMessage(
                    AuthTokenConvert::toFront($data)
                );
        } catch(\Exception $exception){


            return $this->errorJsonMessage($exception->getMessage(), ErrorCode::NOT_VALID_ACCESS_TOKEN);
        }
    }

    public function logout()
    {
        try {
            /** @var User $user */
            $user = \Auth::user();

            $token = $user->token();
            if ($token) {
                $token->revoke();
            }

            return $this->successJsonMessage(__('cms-users::site.message.user logout'));
        } catch (\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }


}
