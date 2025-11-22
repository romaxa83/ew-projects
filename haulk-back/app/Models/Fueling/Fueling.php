<?php

namespace App\Models\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Enums\Fueling\FuelingSourceEnum;
use App\Enums\Fueling\FuelingStatusEnum;
use App\ModelFilters\Fueling\FuelCardFilter;
use App\ModelFilters\Fueling\FuelingFilter;
use App\Models\Users\User;
use App\Scopes\CompanyScope;
use App\Services\Fueling\Entity\AbstractFuelingValidStatus;
use App\Services\Fueling\Entity\FuelingValidStatusFactory;
use App\Traits\SetCompanyId;
use Database\Factories\Fueling\FuelCardFactory;
use Database\Factories\Fueling\FuelingFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Tags\Tag
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $card
 * @property string|null $invoice
 * @property string|null $transaction_date
 * @property string|null $timezone
 * @property string|null $user
 * @property string|null $location
 * @property string|null $state
 * @property string|null $fees
 * @property string|null $item
 * @property string|null $unit_price
 * @property string|null $quantity
 * @property string|null $amount
 * @property FuelingStatusEnum|string|null $status
 * @property FuelingSourceEnum|string|null $source
 * @property int|null $fuel_card_id
 * @property int|null $user_id
 * @property FuelCardProviderEnum|string $provider
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property boolean $valid
 *
 * @see self::getValidStatusAttribute()
 * @property AbstractFuelingValidStatus validStatus
 *
 * @property int|null broker_id
 * @property int|null carrier_id
 *
 * @see self::driver()
 * @property User|MorphTo|null driver
 *
 * @see self::fuelCard()
 * @property FuelCard fuelCard
 *
 * @see self::fuelingHistory()
 * @property FuelingHistory fuelingHistory
 *
 * @mixin Eloquent
 *
 * @method static FuelingFactory factory(...$parameters)
 */
class Fueling extends Model
{
    use Filterable;
    use SetCompanyId;
    use HasFactory;

    public const TABLE_NAME = 'fueling';

    protected $table = self::TABLE_NAME;

    public const UID_FORMATION_FIELDS = [
        'card',
        'transaction_date',
        'invoice',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'uuid',
        'card',
        'transaction_date',
        'timezone',
        'user',
        'location',
        'state',
        'fees',
        'item',
        'unit_price',
        'quantity',
        'amount',
        'status',
        'source',
        'fuel_card_id',
        'user_id',
        'provider',
    ];

    private ?AbstractFuelingValidStatus $propertyValidStatus = null;

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
        return FuelingFilter::class;
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id',
        );
    }

    public function fuelCard(): BelongsTo
    {
        return $this->belongsTo(FuelCard::class, 'fuel_card_id', 'id');
    }

    public function fuelingHistory(): BelongsTo
    {
        return $this->belongsTo(FuelCard::class, 'fuel_card_id', 'id');
    }

    // todo реализовать
    public function hasRelatedEntities(): bool
    {
        return false;
    }

    public function getValidStatusAttribute(): ?AbstractFuelingValidStatus
    {
        if($this->propertyValidStatus)
        {
            return $this->propertyValidStatus;
        }

        return FuelingValidStatusFactory::create($this);
    }
}
