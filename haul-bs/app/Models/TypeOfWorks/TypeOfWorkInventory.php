<?php

namespace App\Models\TypeOfWorks;

use App\Foundations\Models\BaseModel;
use App\Models\Inventories\Inventory;
use Database\Factories\TypeOfWorks\TypeOfWorkInventoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Eloquent;

/**
 * @property int id
 * @property int type_of_work_id
 * @property int inventory_id
 * @property float quantity
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see TypeOfWorkInventory::typeOfWork()
 * @property TypeOfWork $typeOfWork
 *
 * @see TypeOfWorkInventory::inventory()
 * @property  Inventory inventory
 *
 * @mixin Eloquent
 *
 * @method static TypeOfWorkInventoryFactory factory(...$parameters)
 */
class TypeOfWorkInventory extends BaseModel
{
    use HasFactory;

    public const TABLE = 'type_of_work_inventories';
    protected $table = self::TABLE;

    /** @var array<int, string>  */
    protected $fillable = [
        'type_of_work_id',
        'inventory_id',
        'quantity',
    ];

    protected static function newFactory(): TypeOfWorkInventoryFactory
    {
        return TypeOfWorkInventoryFactory::new();
    }

    public function typeOfWork(): BelongsTo
    {
        return $this->belongsTo(TypeOfWork::class, 'type_of_work_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
