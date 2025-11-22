<?php

namespace App\Models\Recommendation;

use App\Casts\UuidCast;
use App\Models\BaseModel;
use App\Models\Order\Order;
use App\Models\User\Car;
use App\Models\User\User;
use Carbon\Carbon;
use Database\Factories\Recommendation\RecommendationFactory;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $status
 * @property string $uuid                   // uuid присвоенный системой АА
 * @property string $car_uuid               // uuid авто, присвоенный системой АА
 * @property string $order_uuid             // uuid заявки, присвоенный системой АА
 * @property string $qty                    // кол-во
 * @property string $text                   // рекомендация (название работы, номенклатуры)
 * @property string|null $comment           // комментарий
 * @property string|null $rejection_reason  // причина отказа
 * @property string|null $author            // автор
 * @property string|null $executor          // исполнитель
 * @property bool $completed
 * @property string $data                   // все пришедшие данные
 * @property Carbon|null completion_at      // дата выполнения
 * @property Carbon|null relevance_at       // дата актуальности
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @method static RecommendationFactory factory()
 */
class Recommendation extends BaseModel
{
    use HasFactory;

    public const STATUS_NEW  = 1;   // только создана
    public const STATUS_USED = 2;   // на основе нее создана заявка
    public const STATUS_OLD  = 3;   // оказалась не использованной или не актуальной

    public const TABLE = 'recommendations';

    protected $table = self::TABLE;

    protected $dates = [
        'completion_at',
        'relevance_at',
    ];

    protected $cats = [
        'uuid' => UuidCast::class,
        'data' => 'array',
    ];

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isUsed(): bool
    {
        return $this->status === self::STATUS_USED;
    }

    public function isOld(): bool
    {
        return $this->status === self::STATUS_OLD;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, "car_uuid", "uuid");
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, "order_uuid", "uuid");
    }

    public function scopeForCurrent(EloquentBuilder $query)
    {
        return $query->where('status', self::STATUS_NEW);
    }
}
