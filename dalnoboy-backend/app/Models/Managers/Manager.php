<?php

namespace App\Models\Managers;

use App\Filters\Managers\ManagerFilter;
use App\Models\BaseModel;
use App\Models\Clients\Client;
use App\Models\Locations\Region;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasPhones;
use Database\Factories\Managers\ManagerFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static ManagerFactory factory()
 */
class Manager extends BaseModel
{
    use HasFactory;
    use HasPhones;
    use Filterable;

    protected $fillable = [
        'first_name',
        'last_name',
        'second_name',
        'region_id',
        'city'
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(ManagerFilter::class);
    }

    public const ALLOWED_SORTING_FIELDS = [
        'full_name',
    ];

    public function getName(): string
    {
        $fullName = sprintf('%s %s %s', $this->last_name, $this->first_name, $this->second_name);

        return trim($fullName);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'manager_id', 'id');
    }
}
