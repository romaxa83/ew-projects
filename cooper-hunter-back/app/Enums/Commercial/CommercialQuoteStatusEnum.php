<?php

namespace App\Enums\Commercial;

use Core\Enums\BaseEnum;

/**
 * @method static static DONE()
 * @method static static PENDING()
 * @method static static FINAL()
 */
class CommercialQuoteStatusEnum extends BaseEnum
{
    public const PENDING    = 'pending';
    public const DONE       = 'done';
    public const FINAL      = 'final';

    public function isPending(): bool
    {
        return $this->is(self::PENDING());
    }

    public function isDone(): bool
    {
        return $this->is(self::DONE());
    }

    public function isFinal(): bool
    {
        return $this->is(self::DONE());
    }
}
