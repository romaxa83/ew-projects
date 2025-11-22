<?php

namespace App\Models;

use App\Traits\Model\HasTranslations;

/**
 * Class BaseHasTranslation
 * @package App\Models
 *
 * @property bool $active
 * @property int $sort
 */
abstract class BaseHasTranslation extends BaseModel
{
    use HasTranslations;
}
