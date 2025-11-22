<?php

namespace App\GraphQL\Mutations\User\User;

use App\Events\Firebase\FcmPush;
use App\Events\User\EditUser;
use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Services\Auth\UserPassportService;
use App\Services\Firebase\FcmAction;
use App\Services\Sms\SmsVerifyService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use App\ValueObjects\Phone;
use GraphQL\Error\Error;

class UserEditPhone extends BaseGraphQL
{
    public function __construct(
        protected UserService $userService,
        protected UserRepository $userRepository,
        protected SmsVerifyService $smsVerifyService,
        protected UserPassportService $passportService
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return User
     */
    public function __invoke($_, array $args): User
    {
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
            // проверяем actionToken
            $obj = $this->smsVerifyService->getAndCheckByActionToken($args['actionToken']);
            $obj->delete();

            $phone = new Phone($args['phone']);
            $comment = $args['comment'] ?? null;

            // проверяем что новый телефон не совпадает со старым
            if($phone->compare($user->phone)){
                throw new \DomainException(__('error.new phone equals old', [
                    'newPhone' => $phone,
                    'oldPhone' => $user->phone
                ]), ErrorsCode::EDIT_PHONE_EQUALS_OLD);
            }

            // проверяем есть ли пользователь с таким телефоном
            if($this->userRepository->getByPhone($phone, [], $user->id)){
                throw new \DomainException(__('error.have user by this phone', ['phone' => $phone]), ErrorsCode::EDIT_PHONE_EXIST);
            }

            // данным парням сразу меняем телефон
            if ($user->isDraft() || $user->isActive()){
                $this->userService->editPhone($user, $phone, $comment);
                event(new FcmPush($user, FcmAction::create(FcmAction::EDIT_PHONE_SUCCESS, [], $user)));
                // разлогиниваем пользователя
                $this->passportService->logout($user);

                return $user;
            }

            if ($user->isVerify()){
                $user = $this->userService->createNewPhoneRecord($user, $phone, $comment);
                // запрос в АА на редактирования пользователя
                if($user->uuid){
                    event(new EditUser($user));
                }
            }

            TelegramDev::info("Edit phone", $user->name);

            return $user;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
