<?php

namespace App\Models\Stores;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Stores\StoreTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property int row_id
 * @property string language
 *
 * @method static StoreTranslationFactory factory(...$parameters)
 */
class StoreTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'store_translations';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'language',
    ];
}
