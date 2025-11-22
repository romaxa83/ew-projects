<?php

namespace App\Enums\Users;

use App\Models\Admins\Admin;
use App\Models\Dealers\Dealer;
use App\Models\OneC\Moderator;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Core\Enums\BaseEnum;

/**
 * Class UserMorphEnum
 * @package App\Enums\Users
 *
 * @method static static USER()
 * @method static static TECHNICIAN()
 * @method static static ADMIN()
 * @method static static MODERATOR()
 * @method static static DEALER()
 */
class UserMorphEnum extends BaseEnum
{
    public const USER = User::MORPH_NAME;
    public const TECHNICIAN = Technician::MORPH_NAME;
    public const ADMIN = Admin::MORPH_NAME;
    public const MODERATOR = Moderator::MORPH_NAME;
    public const DEALER = Dealer::MORPH_NAME;

    public static function getMemberValues(): array
    {
        return array_filter(
            self::getValues(),
            fn(string $item) => !in_array(
                $item,
                [
                    self::ADMIN,
                    self::MODERATOR
                ]
            )
        );
    }

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
