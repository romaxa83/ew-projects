<?php

namespace App\Models\News;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\News\TagTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property int row_id
 * @property string language
 *
 * @method static TagTranslationFactory factory(...$parameters)
 */
class TagTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'tag_translations';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
