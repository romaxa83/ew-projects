<?php

namespace App\Models\Catalog\Features;

use App\Filters\Catalog\FeatureFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Catalog\Features\FeatureFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string|null guid
 * @property int sort
 * @property bool active
 * @property bool display_in_mobile
 * @property bool display_in_web
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see Feature::translations()
 * @property-read Collection|FeatureTranslation[] $translations
 *
 * @see Feature::values()
 * @property-read Collection|Value[] values
 *
 * @method static FeatureFactory factory(...$options)
 */
class Feature extends BaseModel
{
    use HasTranslations;
    use Filterable;
    use HasFactory;
    use ActiveScopeTrait;
    use SetSortAfterCreate;

    public const TABLE = 'catalog_features';

    protected $table = self::TABLE;

    protected $fillable = [
        'display_in_mobile',
        'display_in_web',
        'display_in_filter',
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'display_in_mobile' => 'boolean',
        'display_in_web' => 'boolean',
        'display_in_filter' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return FeatureFilter::class;
    }

    public function values(): HasMany|Value
    {
        return $this->hasMany(Value::class, 'feature_id', 'id');
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class, 'metric_id', 'id');
    }
}
