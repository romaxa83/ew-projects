<?php

namespace App\Models\News;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\News\NewsTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property string short_description
 * @property int row_id
 * @property string language
 *
 * @method static NewsTranslationFactory factory(...$parameters)
 */
class NewsTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'news_translations';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'title',
        'description',
        'short_description',
        'language',
        'seo_title',
        'seo_description',
        'seo_h1',
    ];
}
