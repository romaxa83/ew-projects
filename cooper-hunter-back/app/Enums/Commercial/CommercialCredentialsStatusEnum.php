<?php

namespace App\Enums\Commercial;

use Core\Enums\BaseEnum;

/**
 * @method static static NEW()
 * @method static static APPROVED()
 * @method static static DENIED()
 */
class CommercialCredentialsStatusEnum extends BaseEnum
{
    public const NEW = 'new';
    public const APPROVED = 'approved';
    public const DENIED = 'denied';

    public function isNew(): bool
    {
        return $this->is(self::NEW());
    }
}