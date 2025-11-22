<?php

namespace App\Models\News;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use Database\Factories\News\TagFactory;

/**
 * @property int id
 * @property string color
 *
 * @method static TagFactory factory(...$parameters)
 */
class Tag extends BaseModel
{
    use HasFactory;
    use HasTranslations;

    public const TABLE = 'tags';

    public $timestamps = false;

    protected $table = self::TABLE;
}
