<?php

namespace App\Enums\Companies;

use Core\Enums\BaseEnum;

/**
 * @method static static DRAFT()
 * @method static static APPROVE()
 * @method static static REGISTER()
 */
class CompanyStatus extends BaseEnum
{
    public const DRAFT    = 'draft';
    public const APPROVE  = 'approve';
    public const REGISTER = 'register';

    public function isDraft(): bool
    {
        return $this->is(self::DRAFT());
    }

    public function isApprove(): bool
    {
        return $this->is(self::APPROVE());
    }

    public function isRegister(): bool
    {
        return $this->is(self::REGISTER());
    }
}

