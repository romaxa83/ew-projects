<?php

namespace App\Services;

use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Traits\Auth\EmailVerificationTrait;
use Exception;

class VerificationService
{
    use EmailVerificationTrait;


    /** @throws Exception */
    public function getLinkForEmailReset(Admin|Employee $user): string
    {
        return trim(config('front-routes.forgot-password'), '/') . '?token=' . $this->encryptEmailToken($user);
    }

    public function getLinkForPasswordReset(Admin|Employee $user): string
    {
        return trim(config('front-routes.forgot-password'), '/') . '?token=' . $this->encryptEmailToken($user);
    }
}
