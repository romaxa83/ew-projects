<?php

namespace WezomCms\Users\Models;

use Eloquent;
use Exception;
use Greabock\Tentacles\EloquentTentacle;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\UserAddress;
use WezomCms\Users\Enums\BonusHistoryType;
use WezomCms\Users\Notifications\ResetPassword;
use WezomCms\Users\Notifications\ResetPasswordByCode;
use WezomCms\Users\Notifications\VerifyAccount;

/**
 * \WezomCms\Users\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string $patronymic
 * @property string|null $phone
 * @property bool $phone_verified
 * @property string $lang
 * @property int $status
 * @property string|null $email
 * @property string|null $email_verified_at
 * @property string|null $registered_through
 * @property string $password
 * @property bool $active
 * @property string|null $remember_token
 * @property int|null $temporary_code
 * @property int|null $fcm_token
 * @property int|null $device_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $ref_id
 * @property int $bonus
 *
 * @see User::wishlist()
 * @property-read Collection|Product[] $wishlist
 *
 * @see User::inviter()
 * @property-read User|null $inviter
 *
 * @see User::referrals()
 * @property-read Collection $referrals
 *
 * @see User::inviterBonusHistory()
 * @property-read Collection|BonusHistory[] $inviterBonusHistory
 *
 * @see User::referralBonusHistory()
 * @property-read Collection|BonusHistory[] $referralBonusHistory
 *
 * @see User::addresses()
 * @property-read Collection|UserAddress[] $addresses
 *
 * @see User::orders()
 * @property-read Collection|Order[] $orders
 *
 * @property-read string|string[]|null $clear_phone
 * @property-read string $full_name
 * @property-read string $masked_phone
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read Collection|SocialAccount[] $socialAccounts
 * @method static Builder|User filter($input = array(), $filter = null)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|User query()
 * @method static Builder|User simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|User whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLike($column, $value, $boolean = 'and')
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePatronymic($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User whereRegisteredThrough($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereActive($value)
 * @method static Builder|User whereSurname($value)
 * @method static Builder|User whereTemporaryCode($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static Builder|User whereManagerId($value)
 * @method static Builder|User active()
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use Filterable;
    use HasFactory;
    use Notifiable;
    use EloquentTentacle;

    public const EMAIL = 'email';
    public const PHONE = 'phone';
    public const TEMPORARY_CODE_LENGTH = 4;

    public const MORPH_NAME = 'user';
    public const TABLE = 'users';
    protected $table = self::TABLE;

    protected $fillable = [
        'active',
        'name',
        'surname',
        'patronymic',
        'email',
        'phone',
        'registered_through',
        'password',
        'bonus',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'active' => 'bool',
        'phone_verified' => 'bool'
    ];

    // переопределение метода для паспорт

    /**
     * @param string $value
     * @return string
     */
    public static function emailOrPhone($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? self::EMAIL : self::PHONE;
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(self::class, 'ref_id', 'id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(self::class, 'ref_id', 'id');
    }

    public function inviterBonusHistory(): HasMany
    {
        return $this->hasMany(BonusHistory::class, 'inviter_id')->latest();
    }

    public function referralBonusHistory(): HasMany
    {
        return $this->hasMany(BonusHistory::class, 'referral_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function createCorrectionTransaction(int $sum): void
    {
        $this
            ->inviterBonusHistory()
            ->create([
                'type' => $sum > 0 ? BonusHistoryType::ADJUSTMENT_PLUS : BonusHistoryType::ADJUSTMENT_MINUS,
                'bonus' => abs($sum),
            ]);

        $this->updateBonusSum();
    }

    public function getPositiveBonusSum(): int
    {
        return $this->inviterBonusHistory
            ->sum(function (BonusHistory $bonusHistory) {
                return $bonusHistory->isPositive() ? $bonusHistory->bonus : 0;
            });
    }

    public function getNegativeBonusSum(): int
    {
        return $this->inviterBonusHistory
            ->sum(function (BonusHistory $bonusHistory) {
                return $bonusHistory->isNegative() ? $bonusHistory->bonus : 0;
            });
    }

    public function getBonusSum(): int
    {
        return $this->inviterBonusHistory
            ->sum(function (BonusHistory $bonusHistory) {
                return $bonusHistory->isPositive()
                    ? $bonusHistory->bonus
                    : -$bonusHistory->bonus;
            });
    }

    public function updateBonusSum(): void
    {
        $this->load('inviterBonusHistory');
        $newBonus = $this->getBonusSum();

        if ($newBonus < 0) {
            $newBonus = 0;
        }

        $this->bonus = $newBonus;
        $this->save();
    }

    public function getReferralBonusSum(): int
    {
        return $this->referralBonusHistory
            ->sum(function (BonusHistory $transaction) {
                return $transaction->bonus;
            });
    }

    public function referralBonusLeft(): bool
    {
        $bonusLimit = settings(
            'referrals.site.referral_bonus_limit',
            config('cms.users.users.referrals.bonus_limit', 10)
        );

        return $this->referralBonusHistory()->count() < $bonusLimit;
    }

    /**
     * @param string|null $search
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public static function search(string $search = null, int $limit = 10)
    {
        $query = self::query();

        if ($search) {
            $query->orWhere(
                function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . Helpers::escapeLike($search) . '%')
                        ->orWhere('patronymic', 'LIKE', '%' . Helpers::escapeLike($search) . '%')
                        ->orWhere('surname', 'LIKE', '%' . Helpers::escapeLike($search) . '%');
                }
            );
        }

        return $query->paginate($limit);
    }

    public function findForPassport($username)
    {
        return self::where('id', $username)->first();
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function wishlist(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'user_wishlist',
            'user_id',
            'product_id'
        );
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    public function sendEmailVerificationNotification(): void
    {
        try {
            $this->notify(new VerifyAccount());
        } catch (Exception $e) {
            logger(
                'sendEmailVerificationNotification exception',
                [
                    'action' => 'Send the email verification notification',
                    'user' => $this->id,
                    'message' => $e->getMessage(),
                ]
            );

            // If there was an error sending SMS - mark user as verified.
            if ($this->markEmailAsVerified()) {
                event(new Verified($this));
            }
        }
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill(
            [
                'email_verified_at' => $this->freshTimestamp(),
                'temporary_code' => null,
            ]
        )->save();
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the password reset notification.
     *
     * @return bool
     */
    public function sendPasswordResetByCodeNotification()
    {
        try {
            $this->notify(new ResetPasswordByCode());

            return true;
        } catch (Exception $e) {
            logger(
                'sendPasswordResetByCodeNotification exception',
                [
                    'action' => 'Send the password reset notification',
                    'user' => $this->id,
                    'message' => $e->getMessage(),
                ]
            );

            return false;
        }
    }

    public function routeNotificationForTurboSms(): null|string
    {
        return $this->phone;
    }

    public function routeNotificationForESputnik(): null|string
    {
        return $this->phone;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function generateTemporaryCode(): bool
    {
        $length = self::TEMPORARY_CODE_LENGTH;
        $this->temporary_code = random_int(10 ** ($length - 1), 10 ** $length - 1);

        return $this->save();
    }

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([$this->surname, $this->name, $this->patronymic]));
    }

    /**
     * @return string|string[]|null
     */
    public function getClearPhoneAttribute()
    {
        return remove_phone_mask($this->phone);
    }

    public function getMaskedPhoneAttribute(): string
    {
        return apply_phone_mask($this->phone);
    }

    public function canUseBonuses(): bool
    {
        return $this->bonus > 0;
    }

    public function addBonus(int $bonus): self
    {
        $this->bonus = max($this->bonus + $bonus, 0);

        return $this;
    }

    // здесь нужно добавить проверку, возможно ли удалить профиль
    public function canDeleteProfile(): bool
    {
        return true;
    }
}
