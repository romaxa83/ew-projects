<?php

namespace App\Models\Catalog\Manuals;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Manuals\ManualGroupTranslationFactory;

/**
 * @property string title
 * @property int row_id
 * @property string language
 *
 * @method static ManualGroupTranslationFactory factory(...$parameters)
 */
class ManualGroupTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'manual_group_translations';

    public $timestamps = false;

    protected $table = self::TABLE;
}
