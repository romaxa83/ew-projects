<?php

namespace App\Enums\Commercial\Commissioning;

use Core\Enums\BaseEnum;

/**
 * @method static static REQUIRED()
 * @method static static NOT_REQUIRED()
 * @method static static NOT_NECESSARY()
 */

class AnswerPhotoType extends BaseEnum
{
    public const REQUIRED      = 'required';        // обязательно
    public const NOT_REQUIRED  = 'not_required';    // не нужен (не выводить загрузчик)
    public const NOT_NECESSARY = 'not_needed';      // не обязательно, можно загрузить , можно не грузить
}

