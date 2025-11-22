<?php

namespace App\Services\Admins;

use App\Exceptions\Auth\TokenEncryptException;
use App\Models\Admins\Admin;
use App\Traits\Auth\EmailVerificationTrait;
use Exception;

class AdminVerificationService
{
    use EmailVerificationTrait;

    /**
     * @throws Exception
     * @throws TokenEncryptException
     */
    public function getLinkForEmailReset(Admin $admin, string $link): string
    {
        return trim($link, '/').'/'.$this->encryptEmailToken($admin);
    }
}
