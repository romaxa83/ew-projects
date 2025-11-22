<?php

namespace App\Models\Catalog\Troubleshoots;

use App\Filters\Catalog\Troubleshoots\GroupFilter;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Catalog\Troubleshoots\GroupFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int sort
 * @property bool active
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property Collection|GroupTranslation[] $translations
 *
 * @see Group::troubleshoots()
 * @property-read Collection|Troubleshoot[] troubleshoots
 *
 * @method static GroupFactory factory(...$options)
 */
class Group extends BaseModel
{
    use HasTranslations;
    use Filterable;
    use HasFactory;
    use ActiveScopeTrait;
    use SetSortAfterCreate;

    public const TABLE = 'catalog_troubleshoot_groups';
    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return GroupFilter::class;
    }

    public function troubleshoots(): HasMany
    {
        return $this->HasMany(Troubleshoot::class, 'group_id', 'id');
    }

    public function products(): BelongsToMany|Product
    {
        return $this->belongsToMany(
            Product::class,
            'catalog_product_troubleshoot_groups_pivot',
            'troubleshoot_group_id',
            'product_id',
        )
            ->oldest('sort');
    }

}
