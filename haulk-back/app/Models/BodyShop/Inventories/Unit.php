<?php

namespace App\Models\BodyShop\Inventories;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\Inventories\Units
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property bool $accept_decimals
 *
 * @see Ctagory::inventories()
 * @property Inventory[]|HasMany $inventories
 *
 * @mixin Eloquent
 */
class Unit extends Model
{
    public const TABLE_NAME = 'bs_inventory_units';

    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'accept_decimals',
    ];

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
