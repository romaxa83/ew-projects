<?php

namespace App\Models\Agreement;

use App\Casts\CarNumberCast;
use App\Casts\CarVinCast;
use App\Casts\PhoneCast;
use App\Models\BaseModel;
use App\Models\Dealership\Dealership;
use App\Models\Order\Order;
use App\Models\User\Car;
use App\Models\User\OrderCar\OrderStatus;
use App\Models\User\User;
use App\Types\Order\Status;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $status
 * @property string $uuid
 * @property string $user_uuid
 * @property string $car_uuid
 * @property string $phone
 * @property string $number
 * @property string $vin
 * @property string|null $author
 * @property string|null $author_phone
 * @property string|null $dealership_alias
 * @property string|null $base_order_uuid   // uuid заявки на основе которой создано сог. работы
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property Carbon|null accepted_at     // дата когда была потвержденна от (после принятия пользователем), используеться при фильтрации
 *
 * @property-read User|null user
 * @property-read Car|null car
 * @property-read Order|null order
 * @property-read Dealership|null dealership
 * @property-read Order|null baseOrder
 * @property-read Job|Collection jobs
 * @property-read Part|Collection parts
 */
class Agreement extends BaseModel
{
    use HasFactory;

    public const STATUS_NEW     = 1;   // только создана (пришла от AA)
    public const STATUS_USED    = 2;   // принята пользователем (Отправлен запрос в АА)
    public const STATUS_VERIFY  = 3;   // потверждена AA
    public const STATUS_ERROR   = 4;   // произошла ошибка при принятии в системе AA

    public const TABLE = 'agreements';
    protected $table = self::TABLE;

    protected $casts = [
        'phone' => PhoneCast::class,
        'number' => CarNumberCast::class,
        'vin' => CarVinCast::class,
    ];

    protected $dates = [
        'accepted_at',
    ];

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, "car_uuid", "uuid");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_uuid", "uuid");
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, "uuid", "uuid");
    }

    public function baseOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, "base_order_uuid", "uuid");
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class, "dealership_alias", "alias");
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function scopeUserId(EloquentBuilder $query, $userID)
    {
        return $query->with('user')
            ->whereHas('user', fn ($q) => $q->where('id', $userID));
    }

    public function scopeForCurrent(EloquentBuilder $query)
    {
        return $query
            ->where('status', self::STATUS_NEW);
    }

    public function scopeForVerify(EloquentBuilder $query)
    {
        return $query->where('status', self::STATUS_VERIFY)
            ->whereHas('baseOrder', fn ($q) => $q->where('status', '!=', Status::CLOSE));
    }

    public function scopePeriod(EloquentBuilder $query, $period)
    {
        $today = CarbonImmutable::today();
        $from = CarbonImmutable::now();
        $to = $today->addDay();

        if($period == Order::PERIOD_TODAY){
            return $query->whereBetween('accepted_at', [$from, $to]);
        }
        if($period == Order::PERIOD_INCOMING){
            return $query->where('accepted_at', '>', $to);
        }

        return $query;
    }
}

