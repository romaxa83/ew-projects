<?php

namespace App\Models\Inventories\Features;

use App\Foundations\Models\BaseModel;
use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Inventories\FeatureFilter;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\InventoryFeature;
use Database\Factories\Inventories\Features\FeatureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Eloquent;

/**
 * @property int id
 * @property bool active
 * @property string name
 * @property string slug
 * @property bool multiple
 * @property bool position
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see self::inventories()
 * @property Inventory[]|BelongsToMany inventories
 *
 * @see self::values()
 * @property Value[]|HasMany values
 *
 * @see self::inventoryValues()
 * @property Value[]|BelongsToMany inventoryValues
 *
 * @mixin Eloquent
 *
 * @method static FeatureFactory factory(...$parameters)
 */
class Feature extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'inventory_features';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'inventory_feature';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'active' => 'boolean',
        'multiple' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(FeatureFilter::class);
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function values(): HasMany
    {
        return $this->hasMany(Value::class)->orderBy('position');
    }

    public function inventoryValues(): BelongsToMany
    {
        return $this->belongsToMany(
            Value::class,
            InventoryFeature::TABLE,
            'feature_id',
            'value_id'
        )
            ->wherePivot('inventory_id', $this->pivot->inventory_id)
            ->orderBy('position')
            ;
    }

    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(
            Inventory::class,
            InventoryFeature::TABLE,
            'feature_id',
            'inventory_id'
        );
    }

    public function hasInventoryRelation(): bool
    {
        return $this->inventories()->exists();
    }
}
