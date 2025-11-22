<?php

namespace App\Enums\Commercial;

use Core\Enums\BaseEnum;

/**
 * @method static static CREATED()
 * @method static static PENDING()
 */
class CommercialProjectStatusEnum extends BaseEnum
{
    public const CREATED = 'created';
    public const PENDING = 'pending';

    public function isPending(): bool
    {
        return $this->is(self::PENDING());
    }

    public function isCreated(): bool
    {
        return $this->is(self::CREATED());
    }
}