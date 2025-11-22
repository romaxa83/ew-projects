<?php

namespace App\Models\Catalog\Videos;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Videos\GroupTranslationFactory;

/**
 * @property int id
 * @property string slug
 * @property string title
 * @property null|string description
 * @property int row_id
 * @property string|null language
 *
 * @method static GroupTranslationFactory factory(...$options)
 */
class GroupTranslation extends BaseTranslation
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'catalog_video_group_translations';

    protected $table = self::TABLE;

    protected $fillable = [
        'slug',
        'row_id',
        'language',
        'title',
        'description',
    ];
}
