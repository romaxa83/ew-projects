<?php

namespace App\Models\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\Models\BasePivot;
use App\Models\Catalog\Products\Product;
use App\Traits\SimpleEloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int warranty_registration_id
 * @property int product_id
 * @property string serial_number
 *
 * @see WarrantyRegistrationUnitPivot::scopeNotDeleted()
 * @method Builder|static notDeleted()
 */
class WarrantyRegistrationUnitPivot extends BasePivot
{
    use SimpleEloquent;

    public $timestamps = false;

    public const TABLE = 'warranty_registration_units_pivot';
    protected $table = self::TABLE;


    protected $fillable = [
        'product_id',
        'serial_number',
    ];

    public function warrantyRegistration(): BelongsTo|WarrantyRegistration
    {
        return $this->belongsTo(WarrantyRegistration::class);
    }

    public function product(): BelongsTo|Product
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeNotDeleted(Builder|self $builder): void
    {
        $builder->whereHas('warrantyRegistration', function ($b) {
            return $b->where('warranty_status', '!=', WarrantyStatus::DELETE);
        });
    }
}
