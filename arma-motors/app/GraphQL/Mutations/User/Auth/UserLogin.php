<?php

namespace App\GraphQL\Mutations\User\Auth;

use App\DTO\User\UserDTO;
use App\DTO\User\UserEditDTO;
use App\Exceptions\ErrorsCode;
use App\Exceptions\UserAuthException;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Repositories\Passport\OAuthRepository;
use App\Repositories\User\UserRepository;
use App\Services\AA\RequestService;
use App\Services\Auth\MobileToken;
use App\Services\Auth\UserPassportService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use App\Traits\GraphqlResponse;
use App\ValueObjects\Phone;
use Firebase\JWT\JWT;
use GraphQL\Error\Error;
use PHPUnit\Exception;

class UserLogin extends BaseGraphQL
{
    public function __construct(
        protected UserPassportService $passportService,
        protected UserRepository $userRepository,
        protected UserService $userService,
        protected OAuthRepository $authRepository,
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return User|string
     */
    public function __invoke($_, array $args): array|string
    {
        $guard = \Auth::guard(User::GUARD);
        try {
            $phone = new Phone($args['phone']);
            $password = $args['password'];

            $user = $this->validateAndGetUser($phone, $password);

            //@todo реализация разлогина если аторизация с другого девайса
            //@todo когда подключиться миша сделать поля deviceId и dropCurrentSession, обязательными
            $this->ifAnotherDevice($user, $args);

            $tokens = arrayKeyToCamel($this->passportService->auth($user->id, $password));

            // обновляем у пользователя fcmToken, deviceId если пришли с логином
            $user = $this->userService->update(UserEditDTO::byArgs($args), $user);

            $guard->setUser($user);

            // @todo dev-telegram
            TelegramDev::info('Пользователь авторизован', $user->name);

            $tokens['salt'] = $user->salt;

            return array_merge($tokens, ['user' => $user]);
        }
        catch (UserAuthException $e){
            TelegramDev::error(__FILE__, $e, null, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
        catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e, ErrorsCode::AUTH_UNDEFINED_ERROR);
        }
    }

    private function validateAndGetUser(Phone $phone, string $password): ?User
    {
        $user = $this->userRepository->getByPhone($phone);

        if(!$user){
            throw new UserAuthException(__('auth.wrong_user_login_credentials'), ErrorsCode::LOGIN_WRONG_CREDENTIALS);
        }

        if(!$user->phone_verify){
            throw new UserAuthException(__('auth.not verify phone'), ErrorsCode::LOGIN_PHONE_NOT_VERIFY);
        }

        if(!password_verify($password, $user->password)){
            throw new UserAuthException(__('auth.wrong_user_login_credentials'), ErrorsCode::LOGIN_WRONG_CREDENTIALS);
        }

        return $user;
    }

    private function ifAnotherDevice($user, $args): void
    {
        if(isset($args['deviceId']) && isset($args['dropCurrentSession'])){
            $deviceId = $args['deviceId'];
            $dropCurrentSession = $args['dropCurrentSession'];

            if(null === $user->device_id){
                $this->userService->setDeviceId($user, $deviceId);
            } else {
                // если есть deviceId и не совпадает с текущим
                if(!$user->checkDeviceId($deviceId)){
                    $record = $this->authRepository->authUserRow($user->id);
                    // если есть активный сеанс, а dropCurrentSession=false - кидаем ошибку с кодом 1
                    if($record && !filter_var($dropCurrentSession,FILTER_VALIDATE_BOOLEAN)){
                        throw new UserAuthException(__('message.user.active session to another device'), ErrorsCode::LOGIN_HAS_ACTIVE_SESSION);
                    }
                    // если есть активный сеанс, а dropCurrentSession=true - затираем активные токены, делаем новые
                    if($record && filter_var($dropCurrentSession,FILTER_VALIDATE_BOOLEAN)){
                        $this->authRepository->deleteRefreshTokenByAuthId($record->id);
                        $this->authRepository->deleteAuthUserRow($user->id);
                        // перезаписываем новый deviceId
                        $this->userService->setDeviceId($user, $deviceId);
                    }
                }
            }
        }
    }
}
