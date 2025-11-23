<?php

namespace WezomCms\Users\Models;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use WezomCms\Users\Enums\BonusHistoryType;

/**
 * \WezomCms\Users\Models\BonusHistory
 *
 * @property int $id
 * @property int $inviter_id
 * @property int|null $order_id
 * @property int|null $referral_id
 * @property int $bonus
 * @property int|null $bonus_count
 * @property BonusHistoryType $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see BonusHistory::inviter()
 * @property-read User $inviter
 *
 * @see BonusHistory::referral()
 * @property-read User|null $referral
 *
 * @see BonusHistory::scopeType()
 * @method static Builder|BonusHistory type()
 *
 * @see BonusHistory::scopeUseType()
 * @method static Builder|BonusHistory useType()
 *
 * @mixin Eloquent
 */
class BonusHistory extends Model
{
    use HasFactory;

    public const TABLE = 'bonus_history';
    protected $table = self::TABLE;

    protected $fillable = [
        'type',
        'order_id',
        'inviter_id',
        'referral_id',
        'bonus',
        'bonus_count',
    ];

    protected $casts = [
        'type' => BonusHistoryType::class,
    ];

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referral_id');
    }

    public function scopeType(Builder $query, BonusHistoryType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeUseType(Builder $query): Builder
    {
        return $query->type(BonusHistoryType::USE());
    }

    public function scopeAccrualType(Builder $query): Builder
    {
        return $query->type(BonusHistoryType::ACCRUAL());
    }

    public function isPositive(): bool
    {
        return $this->type->isPositive();
    }

    public function isNegative(): bool
    {
        return $this->type->isNegative();
    }

    public function getFrontTitle(): string
    {
        return match ($this->type->value) {
            BonusHistoryType::USE => __('cms-orders::site.Order :number', [ 'number' => $this->order_id ]),
            BonusHistoryType::ACCRUAL => $this->referral->full_name ?? __('cms-users::site.referrals.Bonus accrue'),
            BonusHistoryType::ADJUSTMENT_PLUS => __('cms-users::site.referrals.Bonus accrue'),
            BonusHistoryType::ADJUSTMENT_MINUS => __('cms-users::site.referrals.Bonus сancellation'),
            default => throw new Exception('Unexpected match value'),
        };
    }
}
