<?php

namespace App\Models\Orders\BS;

use App\Foundations\Models\BaseModel;
use Database\Factories\Orders\BS\TypeOfWorkFactory;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Eloquent;

/**
 * @property int id
 * @property string name
 * @property string duration
 * @property string hourly_rate
 * @property int order_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see TypeOfWork::inventories()
 * @property TypeOfWorkInventory[]|HasMany $inventories
 *
 * @mixin Eloquent
 *
 * @method static TypeOfWorkFactory factory(...$parameters)
 */
class TypeOfWork extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'bs_order_type_of_works';
    protected $table = self::TABLE;

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'duration',
        'hourly_rate',
        'order_id',
    ];

    public function inventories(): HasMany
    {
        return $this->hasMany(TypeOfWorkInventory::class, 'type_of_work_id');
    }

    public function getAmount(): float
    {
        $amount = $this->getInventoryAmount();

        $amount += $this->getLaborAmount();

        return $amount;
    }

    public function getInventoryAmount(): float
    {
        $amount = 0;

        foreach ($this->inventories as $inventory) {
            $amount += $inventory->quantity * $inventory->price;
        }

        return $amount;
    }

    public function getLaborAmount(): float
    {
        return $this->getConvertedDuration() * $this->hourly_rate;
    }

    protected function getConvertedDuration(): float
    {
        [$hours, $minutes] = explode(':', $this->duration);

        return $hours + $minutes/60;
    }
//
//    public function newCollection(array $models = []): TypesOfWorkCollection
//    {
//        return TypesOfWorkCollection::make($models);
//    }
//
//    public function getRelationsForDiff(): array
//    {
//        return [
//            'inventories' => $this->inventories,
//        ];
//    }
}

