<?php

namespace App\Enums\Saas\GPS\Request;

use App\Enums\BaseEnum;

/**
 * @method static static BACKOFFICE()
 * @method static static CRM()
 */

// откуда был запроса на добавление девайсов, бек-офис или срм
class DeviceRequestSource extends BaseEnum
{
    public const CRM     = 'crm';
    public const BACKOFFICE = 'backoffice';

    public function isCRM(): bool
    {
        return $this->is(self::CRM());
    }
}
