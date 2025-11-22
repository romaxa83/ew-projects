<?php

namespace App\Models\Catalog\Manuals;

use App\Filters\Catalog\Manuals\ManualGroupFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Catalog\Manuals\ManualGroupFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int sort
 * @property bool active
 * @property bool show_commercial_certified
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see HasTranslations::translation()
 * @property-read ManualGroupTranslation translation
 *
 * @see HasTranslations::translations()
 * @property-read Collection|ManualGroupTranslation[] translations
 *
 * @see ManualGroup::manuals()
 * @property-read Collection|Manual[] manuals
 *
 * @method static ManualGroupFactory factory(...$parameters)
 */
class ManualGroup extends BaseModel
{
    use HasTranslations;
    use HasFactory;
    use Filterable;
    use SetSortAfterCreate;

    public const TABLE = 'manual_groups';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'boolean',
        'show_commercial_certified' => 'boolean',
    ];

    protected $fillable = [
        'sort',
    ];

    public function modelFilter(): string
    {
        return ManualGroupFilter::class;
    }

    public function manuals(): HasMany|Manual
    {
        return $this->hasMany(Manual::class);
    }
}
