<?php

namespace WezomCms\Orders\Models;

use Cache;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\OrderBySort;

/**
 * \WezomCms\Orders\Models\OrderStatus
 *
 * @property int $id
 * @property int $sort
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $color
 * @property-read string $class
 * @property-read integer $amocrm_value_id
 * @property-read Collection|Order[] $orders
 * @property-read Collection|OrderStatusTranslation[] $translations
 * @method static Builder|OrderStatus listsTranslations($translationField)
 * @method static Builder|OrderStatus newModelQuery()
 * @method static Builder|OrderStatus newQuery()
 * @method static Builder|OrderStatus notTranslatedIn($locale = null)
 * @method static Builder|OrderStatus orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|OrderStatus orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|OrderStatus orderByTranslation($key, $sortmethod = 'asc')
 * @method static Builder|OrderStatus query()
 * @method static Builder|OrderStatus sorting()
 * @method static Builder|OrderStatus translated()
 * @method static Builder|OrderStatus translatedIn($locale = null)
 * @method static Builder|OrderStatus whereCreatedAt($value)
 * @method static Builder|OrderStatus whereId($value)
 * @method static Builder|OrderStatus whereSort($value)
 * @method static Builder|OrderStatus whereTranslation($key, $value, $locale = null)
 * @method static Builder|OrderStatus whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|OrderStatus whereUpdatedAt($value)
 * @method static Builder|OrderStatus withTranslation()
 * @mixin Eloquent
 * @mixin OrderStatusTranslation
 */
class OrderStatus extends Model
{
    use Translatable;
    use GetForSelectTrait;
    use HasFactory;
    use OrderBySort;

    public const TABLE = 'order_statuses';

    public const NEW = 1;
    public const DONE = 2;
    public const CANCELED = 3;
    public const READY = 4;
    public const PAID = 5;
    public const SENT = 6;

    protected $table = self::TABLE;

    protected $fillable = [ 'color', 'amocrm_value_id' ];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['name', 'notification_text'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translation'];

    public static function newStatus(): self
    {
        return static::find(static::NEW);
    }

    public static function paidStatus(): self
    {
        return static::find(static::PAID);
    }

    public static function readyStatus(): self
    {
        return static::find(static::READY);
    }

    public static function canceledStatus(): self
    {
        return static::find(static::CANCELED);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'status_id');
    }

    public static function statusesSequence(): SupportCollection
    {
        return Cache::remember(
            'statuses_sequence',
            86400,
            function () {
                $statuses = collect([
                    self::NEW,
                    self::PAID,
                    self::READY,
                    self::SENT,
                    self::DONE,
                ]);

                $allStatuses = self::query()->whereIn('id', $statuses)->get();

                return $allStatuses->sortBy(function (OrderStatus $status) use ($statuses) {
                    return $statuses->search($status->id);
                });
            }
        );
    }

    public function canBeDeleted(): bool
    {
        return !in_array($this->id, [
            self::NEW,
            self::DONE,
            self::CANCELED,
            self::READY,
            self::PAID,
            self::SENT,
        ]);
    }

    public function isStatus(int $status): bool
    {
        return $this->id === $status;
    }

    public function isDone(): bool
    {
        return $this->isStatus(self::DONE);
    }

    public function isReady(): bool
    {
        return $this->isStatus(self::READY);
    }

    public function isNew(): bool
    {
        return $this->isStatus(self::NEW);
    }

    public function isPaid(): bool
    {
        return $this->isStatus(self::PAID);
    }

    public function getClassAttribute(): string
    {
        return match ($this->id) {
            static::DONE => 'is-made',
            static::CANCELED => 'is-cancel',
            static::READY => 'is-ready',
            default => 'is-process',
        };
    }
}
