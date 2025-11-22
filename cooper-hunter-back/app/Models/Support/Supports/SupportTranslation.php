<?php

namespace App\Models\Support\Supports;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Support\Supports\SupportTranslationFactory;

/**
 * @property int id
 * @property string short_description
 * @property string description
 * @property string working_time
 * @property string video_link
 * @property int row_id
 * @property string language
 *
 * @method static SupportTranslationFactory factory(...$parameters)
 */
class SupportTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'support_translations';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'row_id',
        'language',
        'description',
        'short_description',
        'working_time',
        'video_link',
    ];
}
