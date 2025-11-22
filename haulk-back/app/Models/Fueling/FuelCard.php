<?php

namespace App\Models\Fueling;

use App\Collections\Models\Orders\TagsCollection;
use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\ModelFilters\Fueling\FuelCardFilter;
use App\ModelFilters\Tags\TagFilter;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\DiffableInterface;
use App\Models\Orders\Order;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Scopes\CompanyScope;
use App\Traits\Diffable;
use App\Traits\SetCompanyId;
use Database\Factories\Fueling\FuelCardFactory;
use Database\Factories\Tags\TagFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Tags\Tag
 *
 * @property int $id
 * @property int $card
 * @property FuelCardProviderEnum|string $provider
 * @property boolean $active
 * @property string|FuelCardStatusEnum status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deactivated_at
 * @property Carbon|null $deleted_at
 *
 * @property int|null broker_id
 * @property int|null carrier_id
 *
 * @see self::driver()
 * @property User|MorphTo|null driver
 *
 * @see self::fuelCardHistory()
 * @property FuelCardHistory[]|HasMany|null fuelCardHistory
 *
 * @see self::activeHistory()
 * @property FuelCardHistory|HasOne|null activeHistory
 * @mixin Eloquent
 *
 * @method static FuelCardFactory factory(...$parameters)
 */
class FuelCard extends Model
{
    use Filterable;
    use SetCompanyId;
    use HasFactory;
    use SoftDeletes;

    public const TABLE_NAME = 'fuel_cards';

    /**
     * @var array
     */
    protected $fillable = [
        'card',
        'provider',
        'active',
        'status',
        'deactivated_at',
        'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    public function modelFilter(): string
    {
        return FuelCardFilter::class;
    }

    public function driver(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            FuelCardHistory::class,
            'fuel_card_id',
            'id',
            'id',
            'user_id',
        )->where(FuelCardHistory::TABLE_NAME .'.active', true);
    }
    public function activeHistory(): HasOne
    {
        return $this->hasOne(FuelCardHistory::class, 'fuel_card_id', 'id')
            ->where('active', true)->orderByDesc('id');
    }

    public function fuelCardHistory(): HasMany
    {
        return $this->hasMany(FuelCardHistory::class, 'fuel_card_id', 'id');
    }

    // todo реализовать
    public function hasRelatedEntities(): bool
    {
        return false;
    }
}
