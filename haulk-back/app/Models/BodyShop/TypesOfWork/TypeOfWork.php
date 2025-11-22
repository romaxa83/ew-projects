<?php

namespace App\Models\BodyShop\TypesOfWork;

use App\ModelFilters\BodyShop\TypesOfWork\TypeOfWorkFilter;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\TypesOfWork\TypeOfWork
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $duration
 * @property string $hourly_rate;
 *
 * @see TypeOfWork::inventories()
 * @property TypeOfWorkInventory[] $inventories
 *
 * @mixin Eloquent
 */
class TypeOfWork extends Model
{
    use Filterable;

    public const TABLE_NAME = 'bs_types_of_work';

    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'duration',
        'hourly_rate',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(TypeOfWorkFilter::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(TypeOfWorkInventory::class, 'type_of_work_id');
    }

    public function getEstimatedAmount(): float
    {
        $amount = 0;

        foreach ($this->inventories as $inventory) {
            $amount += $inventory->quantity * $inventory->inventory->price_retail;
        }

        $amount += $this->getConvertedDuration() * $this->hourly_rate;

        return $amount;
    }

    protected function getConvertedDuration(): float
    {
        [$hours, $minutes] = explode(':', $this->duration);

        return $hours + $minutes/60;
    }
}
