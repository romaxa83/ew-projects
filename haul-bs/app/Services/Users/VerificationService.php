<?php

namespace App\Services\Users;

use App\Foundations\Models\BaseAuthenticatableModel;
use App\Foundations\Modules\Utils\Tokenizer\Tokenizer;
use App\Foundations\Modules\Utils\Tokenizer\Traits\CodeGenerator;
use App\Foundations\Modules\Utils\Tokenizer\Traits\EncryptToken;
use App\Models\Users\User;
use App\Notifications\Auth\ConfirmRegistrationNotification;
use App\Notifications\Auth\EmailVerificationNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

class VerificationService
{
    use CodeGenerator;
    use EncryptToken;

    public function getLinkForPasswordReset(BaseAuthenticatableModel $model): string
    {
        $token = $this->getTokenForPassword($model);

        return trim(config('routes.front.forgot_password'), '/') . "?token=$token";
    }

    public function getLinkForSetPassword(BaseAuthenticatableModel $model): string
    {
        $token = $this->getTokenForPassword($model);

        $decrypt = Tokenizer::decryptToken($token);

        $expire = CarbonImmutable::createFromTimestamp($decrypt->timeAt)
            ->addMinutes(config('auth.passwords.users.expire'))
            ->timestamp;

        $params = [
            'token' => $token,
            'email' => $model->email->getValue(),
            'expire' => $expire,
//            'is_first_reg' => 'true',
        ];

        $link = trim(config('routes.front.set_password')) . '?' . http_build_query($params);

//        dd($link, trim(config('routes.front.set_password'), '/') . "?token=$token&email={$model->email->getValue()}&expire=$expire&is_first_reg=true");

        return $link;
    }

    public function getTokenForPassword(BaseAuthenticatableModel $model): string
    {
        /** @var $model User */
        $model->update(['password_verified_code' => $this->verificationCode()]);

        return $this->forPassword($model);
    }

    public function requestEmailVerification(User $model)
    {
        if($model->isEmailVerified()){
            throw new \Exception(__('exceptions.user.email.verified'));
        }

        $token = $this->getTokenForEmail($model);
        $link = trim(config('routes.front.email_verification'), '/') . "?token=$token";

        Notification::route('mail', $model->email->getValue())
            ->notify((new EmailVerificationNotification($model, $link)));
    }

    public function sendConfirmRegistration(User $model)
    {
        Notification::route('mail', $model->email->getValue())
            ->notify(
                (new ConfirmRegistrationNotification(
                    $model,
                    $this->getLinkForSetPassword($model)
                ))
            );
    }

    public function getTokenForEmail(BaseAuthenticatableModel $model): string
    {
        /** @var $model User */
        $model->update(['email_verified_code' => $this->verificationCode()]);

        return $this->forEmail($model);
    }
}
