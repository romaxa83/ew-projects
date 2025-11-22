<?php


namespace App\Enums\Permissions;


use Core\Enums\BaseEnum;

/**
 * Class AdminRolesEnum
 * @package App\Enums\Permissions
 *
 * @method static static SUPER_ADMIN()
 * @method static static ADMIN()
 */
class AdminRolesEnum extends BaseEnum
{
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
}
