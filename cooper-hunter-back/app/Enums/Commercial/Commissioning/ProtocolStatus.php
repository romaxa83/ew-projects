<?php

namespace App\Enums\Commercial\Commissioning;

use Core\Enums\BaseEnum;

/**
 * @method static static DRAFT()
 * @method static static PENDING()
 * @method static static ISSUE()
 * @method static static DONE()
 */
class ProtocolStatus extends BaseEnum
{
    public const DRAFT   = 'draft';
    public const PENDING = 'pending';
    public const ISSUE   = 'issue';
    public const DONE    = 'done';
}

