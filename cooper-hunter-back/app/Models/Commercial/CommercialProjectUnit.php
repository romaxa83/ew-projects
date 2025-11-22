<?php

namespace App\Models\Commercial;

use App\Filters\Commercial\CommercialProjectUnitFilter;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\SetSortAfterCreate;
use Carbon\Carbon;
use Database\Factories\Commercial\CommercialProjectUnitFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int commercial_project_id
 * @property int product_id
 * @property string serial_number
 * @property int sort
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see CommercialProjectUnit::product()
 * @property-read Product product
 *
 * @method static CommercialProjectUnitFactory factory(...$parameters)
 */
class CommercialProjectUnit extends BaseModel
{
    use HasFactory;
    use SetSortAfterCreate;
    use Filterable;

    public const TABLE = 'commercial_project_units';
    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
    ];

    public function modelFilter(): string
    {
        return CommercialProjectUnitFilter::class;
    }

    public function product(): BelongsTo|Product
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
