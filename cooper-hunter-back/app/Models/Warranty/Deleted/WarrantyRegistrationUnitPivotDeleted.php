<?php

namespace App\Models\Warranty\Deleted;

use App\Models\BasePivot;
use App\Models\Catalog\Products\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int warranty_registration_deleted_id
 * @property int product_id
 * @property string serial_number
 */
class WarrantyRegistrationUnitPivotDeleted extends BasePivot
{
    public $timestamps = false;

    public const TABLE = 'warranty_registration_units_pivot_deleted';
    protected $table = self::TABLE;


    protected $fillable = [
        'product_id',
        'serial_number',
    ];

    public function warrantyRegistration(): BelongsTo|WarrantyRegistrationDeleted
    {
        return $this->belongsTo(WarrantyRegistrationDeleted::class);
    }

    public function product(): BelongsTo|Product
    {
        return $this->belongsTo(Product::class);
    }
}

