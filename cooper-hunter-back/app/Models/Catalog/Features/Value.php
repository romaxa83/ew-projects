<?php

namespace App\Models\Catalog\Features;

use App\Filters\Catalog\ValueFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Catalog\Features\ValueFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string title
 * @property int feature_id
 * @property int|null metric_id
 * @property int sort
 * @property bool active
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see Value::feature()
 * @property-read Feature feature
 *
 * @see Value::scopeJoinFeature()
 * @method Builder joinFeature(string $type = 'inner')
 *
 * @see Value::scopeAddFeatureName()
 * @method Builder addFeatureName(string $as = 'feature_name', string $shortName = 'feature_short_name')
 *
 * @method static ValueFactory factory(...$options)
 */
class Value extends BaseModel
{
    use Filterable;
    use HasFactory;
    use ActiveScopeTrait;
    use SetSortAfterCreate;

    public const TABLE = 'catalog_feature_values';

    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'active',
        'title',
        'feature_id',
        'metric_id'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return ValueFilter::class;
    }

    public function feature(): BelongsTo|Feature
    {
        return $this->belongsTo(Feature::class);
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class);
    }

    public function scopeJoinFeature(Builder|self $b, string $type = 'inner'): void
    {
        $b->join(
            Feature::TABLE,
            self::TABLE . '.feature_id',
            '=',
            Feature::TABLE . '.id',
            $type
        );
    }

    public function scopeAddFeatureName(
        Builder|self $b,
        string $as = 'feature_name',
        string $shortName = 'feature_short_name'
    ): void {
        $featureTranslation = FeatureTranslation::TABLE;
        $feature = Feature::TABLE;

        $b->joinFeature()
            ->join(
                $featureTranslation,
                $feature . '.id',
                '=',
                $featureTranslation . '.row_id'
            )
            ->where($featureTranslation . '.language', app()->getLocale())
            ->addSelect($featureTranslation . '.title as ' . $as)
            ->addSelect($featureTranslation . '.description as ' . $shortName);
    }
}
