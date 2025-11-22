<?php

namespace App\GraphQL\Mutations\User\Auth;

use App\DTO\User\UserDTO;
use App\Events\Firebase\FcmPush;
use App\Events\User\EmailConfirm;
use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Http\Requests\Rules\UserPhoneUnique;
use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\RequestService;
use App\Services\Auth\UserPassportService;
use App\Services\Email\EmailVerifyService;
use App\Services\Firebase\FcmAction;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use App\Services\User\UserService;
use GraphQL\Error\Error;

class SignUp extends BaseGraphQL
{
    public function __construct(
        protected UserPassportService $passportService,
        protected UserService $userService,
        protected EmailVerifyService $emailVerifyService,
        protected RequestService $requestService,
        protected CarService $carService
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        try {
            $this->validation($args, $this->rules(), ErrorsCode::EDIT_PHONE_EXIST);

            $dto = UserDTO::byArgs($args);

            $user = $this->userService->create($dto);

            if(EmailVerify::userEnabled() && $dto->getEmail()){
                $emailVerify = $this->emailVerifyService->create($user);
                event(new EmailConfirm($user, $emailVerify));
                event(new FcmPush($user, FcmAction::create(FcmAction::EMAIL_VERIFY, [], $user)));
            }

            // делаем запрос в AA
            $this->requestService->getUserByPhone($user);

            // @todo dev-telegram
            TelegramDev::info("Зарегистрировался пользователь - ({$dto->getName()})");

            return $this->getToken($user, $dto->getPassword());
        }
        catch (AARequestException $e){
            \Log::error($e->getMessage());
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);

            return $this->getToken($user, $dto->getPassword());
        }
        catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }

    private function getToken(User $user, $password): array
    {
        $user->refresh();
        // получаем токены
        $tokens = arrayKeyToCamel($this->passportService->auth($user->id, $password));
        $tokens['salt'] = $user->salt;

        return array_merge($tokens, ['user' => $user]);
    }

    private function rules(): array
    {
        return [
            'phone' => ['required', new UserPhoneUnique()],
        ];
    }
}

