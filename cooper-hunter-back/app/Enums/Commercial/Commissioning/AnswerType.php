<?php

namespace App\Enums\Commercial\Commissioning;

use Core\Enums\BaseEnum;

/**
 * @method static static TEXT()
 * @method static static CHECKBOX()
 * @method static static RADIO()
 */
class AnswerType extends BaseEnum
{
    public const CHECKBOX = 'checkbox';
    public const TEXT     = 'text';
    public const RADIO    = 'radio';
}
