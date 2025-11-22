<?php

namespace App\Models\Inventories\Features;

use App\Foundations\Models\BaseModel;
use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Inventories\FeatureValueFilter;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\InventoryFeature;
use Database\Factories\Inventories\Features\ValueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Eloquent;

/**
 * @property int id
 * @property int feature_id
 * @property bool active
 * @property string name
 * @property string slug
 * @property int position
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see self::inventories()
 * @property Inventory[]|BelongsToMany inventories
 *
 * @mixin Eloquent
 *
 * @method static ValueFactory factory(...$parameters)
 */
class Value extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'inventory_feature_values';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'inventory_feature_value';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'active' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(FeatureValueFilter::class);
    }

    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(
            Inventory::class,
            InventoryFeature::TABLE,
            'value_id',
            'inventory_id'
        );
    }

    public function hasInventoryRelation(): bool
    {
        return $this->inventories()->exists();
    }
}
