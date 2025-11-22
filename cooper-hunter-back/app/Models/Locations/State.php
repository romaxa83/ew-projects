<?php

namespace App\Models\Locations;

use App\Filters\Locations\StateFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\QueryCacheable;
use Database\Factories\Locations\StateFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string short_name
 * @property string|null slug
 * @property bool status
 * @property bool hvac_license
 * @property bool epa_license
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int|null country_id
 *
 * @see HasTranslations::translation()
 * @property-read StateTranslation $translation
 *
 * @see HasTranslations::translations()
 * @property-read Collection|StateTranslation[] $translations
 *
 * @property-read Country country
 *
 * @method static StateFactory factory(...$options)
 */
class State extends BaseModel
{
    use QueryCacheable;
    use HasFactory;
    use Filterable;
    use HasTranslations;

    public const TABLE = 'states';

    protected $table = self::TABLE;

    protected $fillable = [
        'short_name',
        'slug',
        'status',
        'hvac_license',
        'epa_license',
        'country_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'hvac_license' => 'boolean',
        'epa_license' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return StateFilter::class;
    }

    public function zipcodes(): HasMany|Zipcode
    {
        return $this->hasMany(Zipcode::class);
    }

    public function country(): BelongsTo|Country
    {
        return $this->belongsTo(Country::class);
    }
}
