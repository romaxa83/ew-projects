<?php

namespace App\Enums\Member;

use App\Models\Admins\Admin;
use App\Models\Dealers\Dealer;
use App\Models\OneC\Moderator;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Core\Enums\BaseEnum;

/**
 *
 * @method static static USER()
 * @method static static TECHNICIAN()
 * @method static static DEALER()
 */
class MemberEnum extends BaseEnum
{
    public const USER = User::MORPH_NAME;
    public const TECHNICIAN = Technician::MORPH_NAME;
    public const DEALER = Dealer::MORPH_NAME;

    public function isUser(): bool
    {
        return $this->value === self::USER;
    }

    public function isTechnician(): bool
    {
        return $this->value === self::TECHNICIAN;
    }

    public function isDealer(): bool
    {
        return $this->value === self::DEALER;
    }
}

