<?php

namespace App\Models\Inventories;

use App\Foundations\Models\BaseModel;
use Database\Factories\Inventories\UnitFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string name
 * @property bool accept_decimals
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property int|null origin_id
 *
 * @see self::inventories()
 * @property Inventory[]|HasMany $inventories
 *
 * @mixin Eloquent
 *
 * @method static UnitFactory factory(...$parameters)
 */
class Unit extends BaseModel
{
    use HasFactory;

    public const TABLE = 'inventory_units';
    protected $table = self::TABLE;


    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'accept_decimals',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'accept_decimals' => 'bool',
    ];

    public function hasRelatedEntities(): bool
    {
        return $this->inventories()->exists();
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'unit_id');
    }
}
