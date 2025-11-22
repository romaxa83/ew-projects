<?php


namespace App\Enums\Permissions;


use BenSampo\Enum\Contracts\LocalizedEnum;
use Core\Enums\BaseEnum;

/**
 * Class GuardsEnum
 * @package App\Enums\Permissions
 *
 * @method static static ADMIN()
 * @method static static USER()
 */
class GuardsEnum extends BaseEnum implements LocalizedEnum
{
    public const ADMIN = 'graph_admin';
    public const USER = 'graph_user';

    /**
     * @return BaseEnum[]
     */
    public function getRoles(): array
    {
        return match ($this->value) {
            self::ADMIN => AdminRolesEnum::getInstances(),
            self::USER => UserRolesEnum::getInstances(),
        };
    }

    public function getPermissions(): array
    {
        return config('grants.matrix.' . $this->value . '.groups');
    }
}
