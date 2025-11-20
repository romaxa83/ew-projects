<?php

namespace App\Models;

use App\Traits\ModelTranslates;

/**
 * @property int id
 * @property string language
 * @property int row_id
 */
abstract class BaseTranslation extends BaseModel
{
    use ModelTranslates;
}
