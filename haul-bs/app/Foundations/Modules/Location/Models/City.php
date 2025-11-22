<?php

namespace App\Foundations\Modules\Location\Models;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Location\Factories\CityFactory;
use App\Foundations\Modules\Location\Filters\CityFilter;
use App\Foundations\Traits\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string name
 * @property string zip
 * @property bool active
 * @property int state_id
 * @property int timezone
 * @property string country_code
 * @property string country_name
 *
 * @see self::state()
 * @property-read State state
 *
 * @method static CityFactory factory(...$parameters)
 */
class City extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'cities';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'zip',
        'active',
        'state_id',
        'timezone',
        'country_code',
        'country_name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return CityFilter::class;
    }

    protected static function newFactory(): CityFactory
    {
        return CityFactory::new();
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
