<?php

namespace App\Enums\Utils\Versioning;

use Core\Enums\BaseEnum;

/**
 * @method static static OK()
 * @method static static UPDATE_RECOMMENDED()
 * @method static static UPDATE_REQUIRED()
 */
class VersionStatusEnum extends BaseEnum
{
    public const OK = 'ok';
    public const UPDATE_RECOMMENDED = 'update_recommended';
    public const UPDATE_REQUIRED = 'update_required';
}