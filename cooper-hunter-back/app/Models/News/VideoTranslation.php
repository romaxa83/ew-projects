<?php

namespace App\Models\News;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\News\VideoTranslationFactory;

/**
 * @property int id
 * @property string video_link
 * @property string title
 * @property string description
 * @property int row_id
 * @property string language
 *
 * @method static VideoTranslationFactory factory(...$parameters)
 */
class VideoTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'video_translations';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'video_link',
        'title',
        'description',
        'language',
        'seo_title',
        'seo_description',
        'seo_h1',
    ];
}
