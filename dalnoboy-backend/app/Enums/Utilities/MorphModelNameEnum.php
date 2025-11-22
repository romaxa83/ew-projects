<?php


namespace App\Enums\Utilities;


use App\Models\Admins\Admin;
use App\Models\Branches\Branch;
use App\Models\Clients\Client;
use App\Models\Drivers\Driver;
use App\Models\Managers\Manager;
use App\Models\Users\User;
use Core\Enums\BaseEnum;

/**
 * Class MorphModelNameEnum
 * @package App\Enums\Utilities
 *
 * @method static static user()
 * @method static static admin()
 * @method static static manager()
 * @method static static client()
 * @method static static driver()
 * @method static static branch()
 */
class MorphModelNameEnum extends BaseEnum
{
    public const user = User::class;
    public const admin = Admin::class;
    public const manager = Manager::class;
    public const client = Client::class;
    public const driver = Driver::class;
    public const branch = Branch::class;
}
