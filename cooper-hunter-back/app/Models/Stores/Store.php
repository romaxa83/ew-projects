<?php

namespace App\Models\Stores;

use App\Filters\Stores\StoreFilter;
use App\Models\BaseHasTranslation;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Stores\StoreFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int sort
 * @property bool active
 * @property string link
 * @property int store_category_id
 *
 * @method static StoreFactory factory(...$parameters)
 */
class Store extends BaseHasTranslation
{
    use Filterable;
    use HasFactory;
    use SetSortAfterCreate;

    public const TABLE = 'stores';

    public $timestamps = false;

    protected $fillable = [
        'sort',
    ];

    public function modelFilter(): string
    {
        return StoreFilter::class;
    }

    public function category(): BelongsTo|StoreCategory
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }
}
