<?php

namespace App\Models\TypeOfWorks;

use App\Foundations\Models\BaseModel;
use App\ModelFilters\TypeOfWorks\TypeOfWorkFilter;
use Database\Factories\TypeOfWorks\TypeOfWorkFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string name
 * @property string duration
 * @property float hourly_rate
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see TypeOfWork::inventories()
 * @property TypeOfWorkInventory[] $inventories
 *
 * @mixin Eloquent
 *
 * @method static TypeOfWorkFactory factory(...$parameters)
 */
class TypeOfWork extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'type_of_works';

    protected $table = self::TABLE;

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'duration',
        'hourly_rate',
    ];

    public const ALLOWED_SORTING_FIELDS = [
        'name',
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
