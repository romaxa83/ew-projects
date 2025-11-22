<?php

namespace App\Models\Inventories;

use App\Foundations\Models\BaseModel;
use Database\Factories\Inventories\InventoryFeatureFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int inventory_id
 * @property int feature_id
 * @property int value_id
 *
 * @mixin Eloquent
 *
 * @method static InventoryFeatureFactory factory(...$parameters)
 */
class InventoryFeature extends BaseModel
{
    use HasFactory;

    public const TABLE = 'inventory_feature_relations';
    protected $table = self::TABLE;

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = ['inventory_id', 'feature_id', 'value_id'];

    /** @var array<int, string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [];
}
