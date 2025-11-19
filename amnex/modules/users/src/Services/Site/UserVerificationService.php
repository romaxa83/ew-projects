<?php

namespace Wezom\Users\Services\Site;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Throwable;
use Wezom\Core\Exceptions\TranslatedException;
use Wezom\Core\Traits\VerificationCodeGenerator;
use Wezom\Users\Exceptions\EmailAlreadyVerifiedException;
use Wezom\Users\Exceptions\InvalidEmailVerificationLink;
use Wezom\Users\Models\User;
use Wezom\Users\Notifications\Site\UserEmailVerificationNotification;

class UserVerificationService
{
    use VerificationCodeGenerator;

    /**
     * @throws Exception
     */
    public function initEmailVerification(User $user): void
    {
        $this->checkEmailVerified($user);

        $this->setEmailVerificationCode($user);

        $link = $this->getLinkForEmailVerification($user);

        $this->sendEmailVerificationNotification($user, $link);
    }

    public function verifyToken(string $token): User
    {
        try {
            $decrypted = $this->decryptToken($token);
        } catch (Throwable $e) {
            throw new InvalidEmailVerificationLink(__('users::exceptions.invalid_verification_link'));
        }

        if (Carbon::parse($decrypted['time'])->addMonth()->timestamp < time()) {
            throw new InvalidEmailVerificationLink(__('users::exceptions.link_lifetime_has_expired'));
        }

        $user = User::find($decrypted['id']);

        if (!$user) {
            throw new TranslatedException(__('users::exceptions.user_not_found'));
        }

        if ($user->getEmailVerificationCode() !== $decrypted['code']) {
            throw new InvalidEmailVerificationLink(__('users::exceptions.invalid_verification_link'));
        }

        return $user;
    }

    /**
     * @throws Exception
     */
    public function checkEmailVerified(User $user): void
    {
        if ($user->isEmailVerified()) {
            throw new EmailAlreadyVerifiedException(__('users::exceptions.email_already_verified'));
        }
    }

    public static function isEmailVerified(User $user): bool
    {
        return $user->isEmailVerified();
    }

    public function setEmailVerificationCode(User $user): void
    {
        $user->setVerificationCode($this->generateVerificationCode());
        $user->save();
    }

    public static function setEmailAsVerified(User $user): void
    {
        if (static::isEmailVerified($user)) {
            return;
        }

        $user->setVerificationCode(null);
        $user->setEmailVerifiedAt(now());
        $user->save();
    }

    public function setEmailAsUnverified(User $user): void
    {
        $user->setVerificationCode(null);
        $user->setEmailVerifiedAt(null);
        $user->save();
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
    public function encryptToken(User $user): string
    {
        $code = $user->getEmailVerificationCode();

        if (empty($code)) {
            throw new Exception('Email verification code is empty.');
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
    protected function getLinkForEmailVerification(User $user): string
    {
        return config('front_routes.frontoffice.email-confirm') . '/' . $this->encryptToken($user);
    }

    protected function sendEmailVerificationNotification(User $user, string $link): void
    {
        $user->notify((new UserEmailVerificationNotification($link))->locale(app()->getLocale()));
    }
}
