<?php

namespace App\Models\Locations;

use App\Filters\Locations\CountryFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use App\Traits\QueryCacheable;
use Database\Factories\Locations\CountryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string alias
 * @property bool active
 * @property bool default
 * @property int sort
 * @property string country_code
 *
 * @see HasTranslations::translation()
 * @property-read CountryTranslation $translation
 *
 * @see HasTranslations::translations()
 * @property-read Collection|CountryTranslation[] $translations
 *
 * @property-read Collection|State[] states
 * @method static CountryFactory factory(...$options)
 */
class Country extends BaseModel
{
    use QueryCacheable;
    use HasFactory;
    use HasTranslations;
    use SetSortAfterCreate;
    use Filterable;

    public const TABLE = 'countries';
    protected $table = self::TABLE;

    public $timestamps = false;

    public const COUNTRY_CODE_US = 'US';

    protected $fillable = [
        'alias',
        'active',
        'default',
        'sort',
        'country_code',
    ];

    protected $casts = [
        'active' => 'boolean',
        'default' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return CountryFilter::class;
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function isUS(): bool
    {
        return $this->country_code === self::COUNTRY_CODE_US;
    }
}

