<?php

namespace App\Enums\Commercial\Commissioning;

use Core\Enums\BaseEnum;

/**
 * @method static static NONE()
 * @method static static DRAFT()
 * @method static static ACCEPT()
 * @method static static REJECT()
 */
class AnswerStatus extends BaseEnum
{
    public const NONE   = 'none';
    public const DRAFT  = 'draft';
    public const ACCEPT = 'accept';
    public const REJECT = 'reject';
}
