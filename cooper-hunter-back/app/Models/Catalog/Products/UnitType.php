<?php

namespace App\Models\Catalog\Products;

use App\Enums\Catalog\Products\ProductUnitType;
use App\Models\BaseModel;
use App\Traits\HasFactory;

/**
 * @property int id
 * @property string name
 */
class UnitType extends BaseModel
{
    use HasFactory;

    public const TABLE = 'product_unit_types';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'name'
    ];

    public function isIndoor(): bool
    {
        return $this->name === ProductUnitType::INDOOR;
    }

    public function isOutdoor(): bool
    {
        return $this->name === ProductUnitType::OUTDOOR;
    }

    public function isMonoblock(): bool
    {
        return $this->name === ProductUnitType::MONOBLOCK;
    }

    public function isAccessory(): bool
    {
        return $this->name === ProductUnitType::ACCESSORY;
    }
}

