<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Notifications\Members\MemberEmailVerification;
use App\Traits\Auth\EmailVerificationTrait;
use Exception;
use Illuminate\Support\Facades\Notification;

class UserVerificationService
{
    use EmailVerificationTrait;

    /** @throws Exception */
    public function verifyEmail(User $user): bool
    {
        $this->assertEmailNotVerified($user);

        $token = $this->encryptEmailToken($user);

        Notification::route('mail', (string)$user->getEmail())
            ->notify(
                (new MemberEmailVerification($user, $token))
                    ->locale(app()->getLocale())
            );

        return true;
    }

    /** @throws Exception */
    public function getLinkForEmailReset(User $user): string
    {
        return trim(config('front_routes.password-forgot'), '/') . '?token=' . $this->encryptEmailToken($user);
    }
}
