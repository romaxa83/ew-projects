<?php

namespace App\Services\Dealers;

use App\Exceptions\Auth\TokenEncryptException;
use App\Models\Dealers\Dealer;
use App\Notifications\Members\MemberEmailVerification;
use App\Traits\Auth\EmailVerificationTrait;
use Exception;
use Illuminate\Support\Facades\Notification;

class DealerVerificationService
{
    use EmailVerificationTrait;

    /**
     * @param Dealer $dealer
     * @return bool
     * @throws TokenEncryptException
     * @throws Exception
     */
    public function verifyEmail(Dealer $dealer): bool
    {
        $this->assertEmailNotVerified($dealer);

        $token = $this->encryptEmailToken($dealer);

        Notification::route('mail', (string)$dealer->getEmail())
            ->notify(
                (new MemberEmailVerification($dealer, $token))
                    ->locale(app()->getLocale())
            );

        return true;
    }

    /**
     * @param Dealer $dealer
     * @return string
     * @throws TokenEncryptException
     */
    public function getLinkForEmailReset(Dealer $dealer): string
    {
        return trim(config('front_routes.password-forgot'), '/') . '?token=' . $this->encryptEmailToken($dealer);
    }
}
