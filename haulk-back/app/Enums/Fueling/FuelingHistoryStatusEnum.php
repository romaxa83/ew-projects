<?php

namespace App\Enums\Fueling;

use App\Enums\BaseEnum;

/**
 * @method static static SUCCESS()
 * @method static static IN_PROGRESS()
 * @method static static IN_QUEUE()
 * @method static static FAILED()
 */

class FuelingHistoryStatusEnum extends BaseEnum
{
    public const SUCCESS   = 'success';
    public const IN_PROGRESS = 'in_progress';
    public const IN_QUEUE = 'in_queue';
    public const FAILED = 'failed';
}

