<?php

namespace App\Models\Locations;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Locations\CountryTranslationFactory;
use Rennokki\QueryCache\Traits\QueryCacheable;

/**
 * @property int id
 * @property string name
 * @property int row_id
 * @property string language
 *
 * @method static CountryTranslationFactory factory(...$options)
 */
class CountryTranslation extends BaseTranslation
{
    use QueryCacheable;
    use HasFactory;

    public const TABLE = 'country_translations';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'language'
    ];

    protected $hidden = [
        'row_id',
    ];
}

