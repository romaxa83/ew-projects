<?php

namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AuthUser extends BaseGraphQL
{
    /**
     * Return information about current user
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return User
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): User
    {
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
//            $f = '{"success":true,"data":{"id":"d9c5ccc1-dda5-11ec-827d-4cd98fc26f11","name":"FLUENCE AU2 D110A 5A Білий № AI6881HE VIN VF1LZBL0E49537082","brand":null,"model":null,"year":"2013","yearDeal":"","vin":"VF1LZBL0E49537082","number":"AI6881HE","owner":"d9c5ccc0-dda5-11ec-827d-4cd98fc26f11","statusCar":false,"personal":false,"buy":false,"orderCar":{"orderNumber":"0","paymentStatusCar":1,"sumDiscount":0,"sum":0},"proxies":[],"verified":true},"message":""}';
//
//            dd(json_to_array($f));
//            event(new FcmPush($user, FcmAction::create(FcmAction::ACTION_TEST)));

            if (null === $user) {
                throw new Error(__('auth.not auth'));
            }

            return $user;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
