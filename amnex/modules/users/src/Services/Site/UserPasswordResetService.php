<?php

namespace Wezom\Users\Services\Site;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Throwable;
use Wezom\Core\Exceptions\TranslatedException;
use Wezom\Core\Traits\VerificationCodeGenerator;
use Wezom\Users\Exceptions\InvalidPasswordResetLink;
use Wezom\Users\Models\User;
use Wezom\Users\Notifications\Site\UserForgotPasswordNotification;

class UserPasswordResetService
{
    use VerificationCodeGenerator;

    /**
     * @throws Exception
     */
    public function initPasswordReset(User $user): void
    {
        $this->setEmailResetCode($user);

        $link = $this->getLinkForEmailReset($user);

        $this->sendForgotPasswordNotification($user, $link);
    }

    public function verifyToken(string $token): User
    {
        try {
            $decrypted = $this->decryptToken($token);
        } catch (Throwable) {
            throw new InvalidPasswordResetLink(__('users::exceptions.invalid_password_reset_link'));
        }

        if (Carbon::parse($decrypted['time'])->addMinutes(30)->timestamp < time()) {
            throw new InvalidPasswordResetLink(__('users::exceptions.link_lifetime_has_expired'));
        }

        $user = User::find($decrypted['id']);

        if (!$user) {
            throw new TranslatedException(__('users::exceptions.user_not_found'));
        }

        if ($user->getPasswordResetCode() !== $decrypted['code']) {
            throw new InvalidPasswordResetLink(__('users::exceptions.invalid_password_reset_link'));
        }

        return $user;
    }

    public function changePassword(User $user, string $password): void
    {
        $user->setPassword($password);
        $user->setPasswordResetCode(null);
        $user->save();
    }

    /**
     * @throws Exception
     */
    protected function setEmailResetCode(User $user): void
    {
        $user->setPasswordResetCode($this->generateVerificationCode());
        $user->save();
    }

    /**
     * @throws Exception
     */
    public function encryptToken(User $user): string
    {
        $code = $user->getPasswordResetCode();

        if (empty($code)) {
            throw new Exception('Email reset code is empty.');
        }

        return Crypt::encryptString(
            json_encode(
                [
                    'id' => $user->id,
                    'time' => time(),
                    'code' => $code
                ],
                JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * @throws Exception
     */
    public function decryptToken(string $token): array
    {
        return json_decode(
            Crypt::decryptString($token),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @throws Exception
     */
    protected function getLinkForEmailReset(User $user): string
    {
        return config('front_routes.frontoffice.password-reset') . '/' . $this->encryptToken($user);
    }

    protected function sendForgotPasswordNotification(User $user, string $link): void
    {
        $user->notify((new UserForgotPasswordNotification($link))->locale(app()->getLocale()));
    }
}
