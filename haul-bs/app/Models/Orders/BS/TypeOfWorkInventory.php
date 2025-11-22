<?php

namespace App\Models\Orders\BS;

use App\Foundations\Models\BaseModel;
use App\Models\Inventories\Inventory;
use Database\Factories\Orders\BS\TypeOfWorkInventoryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int type_of_work_id
 * @property int inventory_id
 * @property float quantity
 * @property float|null price
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see TypeOfWorkInventory::typeOfWork()
 * @property TypeOfWork|BelongsTo $typeOfWork
 *
 * @see TypeOfWorkInventory::inventory()
 * @property Inventory|BelongsTo inventory
 *
 * @mixin Eloquent
 *
 * @method static TypeOfWorkInventoryFactory factory(...$parameters)
 */
class TypeOfWorkInventory extends BaseModel
{
    use HasFactory;

    public const TABLE = 'bs_order_type_of_work_inventories';

    protected $table = self::TABLE;

    /** @var array<int, string>  */
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
//
//    public function newCollection(array $models = []): TypesOfWorkInventoryCollection
//    {
//        return TypesOfWorkInventoryCollection::make($models);
//    }
}
