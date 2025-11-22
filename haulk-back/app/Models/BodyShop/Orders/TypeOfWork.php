<?php

namespace App\Models\BodyShop\Orders;

use App\Collections\Models\BodyShop\Orders\TypesOfWorkCollection;
use App\Models\DiffableInterface;
use App\Traits\Diffable;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\Orders\TypeOfWork
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $duration
 * @property string $hourly_rate;
 * @property int $order_id
 *
 * @see TypeOfWork::inventories()
 * @property TypeOfWorkInventory[] $inventories
 *
 * @mixin Eloquent
 */
class TypeOfWork extends Model implements DiffableInterface
{
    use Filterable;
    use Diffable;

    public const TABLE_NAME = 'bs_order_types_of_work';

    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
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

    public function newCollection(array $models = []): TypesOfWorkCollection
    {
        return TypesOfWorkCollection::make($models);
    }

    public function getRelationsForDiff(): array
    {
        return [
            'inventories' => $this->inventories,
        ];
    }
}
