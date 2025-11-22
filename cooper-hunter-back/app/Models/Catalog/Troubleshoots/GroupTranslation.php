<?php

namespace App\Models\Catalog\Troubleshoots;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use Database\Factories\Catalog\Troubleshoots\GroupTranslationFactory;

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
    use ActiveScopeTrait;

    public $timestamps = false;

    public const TABLE = 'catalog_troubleshoot_group_translations';

    protected $table = self::TABLE;

    protected $fillable = [
        'slug',
        'row_id',
        'language',
        'title',
        'description',
    ];
}
