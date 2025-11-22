<?php

namespace App\Models\BodyShop\Inventories;

use App\ModelFilters\BodyShop\Inventories\CategoryFilter;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\Inventories\Categories
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 *
 * @see Ctagory::inventories()
 * @property Inventory[]|HasMany $inventories
 *
 * @mixin Eloquent
 */
class Category extends Model
{
    use Filterable;

    public const TABLE_NAME = 'bs_inventory_categories';

    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(CategoryFilter::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'category_id');
    }

    public function hasRelatedEntities(): bool
    {
        return $this->inventories()->exists();
    }
}
