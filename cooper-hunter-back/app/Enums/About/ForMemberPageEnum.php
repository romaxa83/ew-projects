<?php

namespace App\Enums\About;

use Core\Enums\BaseEnum;

/**
 * @method static static FOR_HOMEOWNER()
 * @method static static FOR_TECHNICIAN()
 * @method static static REBATES()
 */
class ForMemberPageEnum extends BaseEnum
{
    public const FOR_HOMEOWNER = 'for_homeowner';
    public const FOR_TECHNICIAN = 'for_technician';
    public const REBATES = 'rebates';
}
