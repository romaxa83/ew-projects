<?php

namespace App\Models\Inventories;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Seo\Contracts\HasSeo;
use App\Foundations\Modules\Seo\Traits\InteractsWithSeo;
use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Inventories\BrandFilter;
use Database\Factories\Inventories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Eloquent;

/**
 * @property int id
 * @property string name
 * @property string slug
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see self::inventories()
 * @property Inventory[]|HasMany $inventories
 *
 * @mixin Eloquent
 *
 * @method static BrandFactory factory(...$parameters)
 */
class Brand extends BaseModel implements HasSeo
{
    use HasFactory;
    use Filterable;
    use InteractsWithSeo;

    public const TABLE = 'inventory_brands';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'inventory_brand';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];

    /** @var array<string, string> */
    protected $casts = [];

    public function modelFilter()
    {
        return $this->provideFilter(BrandFilter::class);
    }

    public function hasRelatedEntities(): bool
    {
        return $this->inventories()->exists();
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'brand_id');
    }
}
