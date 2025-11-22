<?php

namespace App\Services\Technicians;

use App\Exceptions\Auth\TokenEncryptException;
use App\Models\Technicians\Technician;
use App\Notifications\Members\MemberEmailVerification;
use App\Traits\Auth\EmailVerificationTrait;
use Exception;
use Illuminate\Support\Facades\Notification;

class TechnicianVerificationService
{
    use EmailVerificationTrait;

    /**
     * @param Technician $technician
     * @return bool
     * @throws TokenEncryptException
     * @throws Exception
     */
    public function verifyEmail(Technician $technician): bool
    {
        $this->assertEmailNotVerified($technician);

        $token = $this->encryptEmailToken($technician);

        Notification::route('mail', (string)$technician->getEmail())
            ->notify(
                (new MemberEmailVerification($technician, $token))
                    ->locale(app()->getLocale())
            );

        return true;
    }

    /**
     * @param Technician $technician
     * @return string
     * @throws TokenEncryptException
     */
    public function getLinkForEmailReset(Technician $technician): string
    {
        return trim(config('front_routes.password-forgot'), '/') . '?token=' . $this->encryptEmailToken($technician);
    }
}
