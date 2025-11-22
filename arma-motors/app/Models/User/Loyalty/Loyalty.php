<?php

namespace App\Models\User\Loyalty;

use App\Helpers\ConvertNumber;
use App\Models\BaseModel;
use App\Models\Catalogs\Car\Brand;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $brand_id
 * @property bool $active
 * @property string $type
 * @property string|null $age
 * @property int $discount
 */

class Loyalty extends BaseModel
{
    public $timestamps = false;

    public const TYPE_SERVICE = 'service';
    public const TYPE_SPARES  = 'spares';
    public const TYPE_BYU     = 'byu';

    public const TABLE_NAME = 'loyalties';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $table = self::TABLE_NAME;

    public function getDiscountFloatAttribute(): float
    {
        return ConvertNumber::fromNumberToFloat($this->discount);
    }

    // reletions

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

}
