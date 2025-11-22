<?php

namespace App\Models\BodyShop\Orders;

use App\Collections\Models\BodyShop\Orders\TypesOfWorkInventoryCollection;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\DiffableInterface;
use App\Traits\Diffable;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\Orders\TypesOfWorkInventory
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $type_of_work_id
 * @property int $inventory_id
 * @property int $quantity
 * @property float $price
 *
 * @see TypeOfWorkInventory::typeOfWork()
 * @property TypeOfWork $typeOfWork
 *
 * @see TypeOfWorkInventory::inventory()
 * @property  Inventory inventory
 *
 * @mixin Eloquent
 */
class TypeOfWorkInventory extends Model implements DiffableInterface
{
    use Diffable;

    public const TABLE_NAME = 'bs_order_type_of_work_inventories';

    protected $table = self::TABLE_NAME;

    /** @var array  */
    protected $fillable = [
        'type_of_work_id',
        'inventory_id',
        'quantity',
        'price',
    ];

    public function typeOfWork(): BelongsTo
    {
        return $this->belongsTo(TypeOfWork::class, 'type_of_work_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id')->withTrashed();
    }

    public function getAmount(): float
    {
        return $this->quantity * $this->price;
    }

    public function getAmountCost(): float
    {
        return $this->quantity * $this->inventory;
    }

    public function newCollection(array $models = []): TypesOfWorkInventoryCollection
    {
        return TypesOfWorkInventoryCollection::make($models);
    }
}
