<?php

namespace App\Models\Stores;

use App\Filters\Stores\StoreCategoryFilter;
use App\Models\BaseHasTranslation;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Stores\StoreCategoryFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property int sort
 * @property bool active
 *
 * @method static StoreCategoryFactory factory(...$parameters)
 */
class StoreCategory extends BaseHasTranslation
{
    use Filterable;
    use HasFactory;
    use SetSortAfterCreate;

    public const TABLE = 'store_categories';

    public $timestamps = false;

    protected $fillable = [
        'sort',
    ];

    public function modelFilter(): string
    {
        return StoreCategoryFilter::class;
    }

    public function stores(): HasMany|Store
    {
        return $this->hasMany(Store::class)->latest('sort');
    }
}
