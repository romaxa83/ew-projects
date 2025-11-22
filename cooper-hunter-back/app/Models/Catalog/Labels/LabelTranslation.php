<?php

namespace App\Models\Catalog\Labels;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Labels\LabelTranslationFactory;

/**
 * @property integer id
 * @property string title
 * @property int row_id
 * @property string language
 *
 * @method static LabelTranslationFactory factory(...$parameters)
 */

class LabelTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'catalog_label_translations';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
