<?php

namespace App\Models\BodyShop\TypesOfWork;

use App\Models\BodyShop\Inventories\Inventory;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\TypesOfWork\TypesOfWorkInventory
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $type_of_work_id
 * @property int $inventory_id
 * @property int $quantity
 *
 * @see TypeOfWorkInventory::typeOfWork()
 * @property TypeOfWork $typeOfWork
 *
 * @see TypeOfWorkInventory::inventory()
 * @property  Inventory inventory
 *
 * @mixin Eloquent
 */
class TypeOfWorkInventory extends Model
{
    public const TABLE_NAME = 'bs_type_of_work_inventories';

    protected $table = self::TABLE_NAME;

    /** @var array  */
    protected $fillable = [
        'type_of_work_id',
        'inventory_id',
        'quantity',
    ];

    public function typeOfWork(): BelongsTo
    {
        return $this->belongsTo(TypeOfWork::class, 'type_of_work_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
