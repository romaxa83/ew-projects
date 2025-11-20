<?php

namespace WezomCms\Users\Services;

use App\Exceptions\SmsTokenExpiredException;
use App\Exceptions\SmsTokenIncorrectException;
use Carbon\Carbon;
use WezomCms\TelegramBot\Events\TelegramDev;
use WezomCms\Users\Events\SmsCodeSend;
use WezomCms\Users\Types\UserStatus;
use WezomCms\Users\UseCase\PhoneToken;
use WezomCms\Users\Models\User;

class PhoneVerifyService
{
    private $useSmsSender;

    private PhoneToken $phoneToken;

    public function __construct(PhoneToken $phoneToken)
    {
        $this->useSmsSender = config('cms.users.users.use_sms_sender');
        $this->phoneToken = $phoneToken;
    }

    /**
     * запрос для верификации номера
     * @param User $user
     * @throws \Exception
     * @throws \Throwable
     */
    public function requestPhoneVerify(User $user, $textRequest = null): ?string
    {
        if(!$user->phone){
            throw new \Exception(__('cms-users::site.exception.user does not have a phone'));
        }

        $user->phone_verified = false;
        $user->phone_verify_token = $this->phoneToken->token();
        $user->phone_verify_token_expire = $this->phoneToken->expired();

        $user->save();

        if(!$this->useSmsSender){
            event(new TelegramDev('sms_sender отключен'));
        } else {
            event(new TelegramDev('sms_sender включен'));
            event(new SmsCodeSend($user, $user->phone_verify_token));
        }
        event(new TelegramDev('код для верификации - '. $user->phone_verify_token));

        $message = $this->useSmsSender
            ? $textRequest
            : $user->phone_verify_token;

        return $message;
    }

    /**
     * верификация номера
     * @param User $user
     * @param $token
     * @throws \Exception
     * @throws \Throwable
     */
    public function phoneVerify(User $user, $token): ?bool
    {

        if($token !== $user->phone_verify_token){
            event(new TelegramDev('код для верификации не корректен'));
            throw new SmsTokenIncorrectException(__('cms-users::site.exception.phone verify token incorrect'));
        }

        $now = Carbon::now();

        if($user->phone_verify_token_expire->lt($now)){
            event(new TelegramDev('код для верификации протух'));
            throw new SmsTokenExpiredException(__('cms-users::site.exception.phone verify token expired'));
        }

        $user->phone_verified = true;
        $user->phone_verify_token = null;
        $user->phone_verify_token_expire = null;
        $user->status = UserStatus::CREATED_VERIFY;

        if($user->save()){
            event(new TelegramDev('пользователь верифицирован'));
            return true;
        }
        return false;
    }
}
