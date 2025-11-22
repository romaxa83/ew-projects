<?php

namespace App\Models\User;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Casts\UuidCast;
use App\Exceptions\ErrorsCode;
use App\Models\AA\AAResponse;
use App\Models\Localization\Language;
use App\Models\Media\Image;
use App\Models\Notification\Fcm;
use App\Models\Order\Order;
use App\Models\Promotion\Promotion;
use App\Models\User\Loyalty\Loyalty;
use App\Models\Verify\EmailVerify;
use App\Traits\Media\ImageRelation;
use App\Traits\Scopes;
use App\Traits\SetPasswordTrait;
use App\Types\Order\Status;
use App\ValueObjects\Phone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @property int $id
 * @property string|null $uuid
 * @property string $name
 * @property string $email
 * @property boolean $email_verify
 * @property string $password
 * @property string $remember_token
 * @property string $phone
 * @property boolean $phone_verify
 * @property int $status
 * @property string $lang
 * @property string $egrpoy
 * @property string $device_id
 * @property string $fcm_token
 * @property string $new_phone
 * @property string $new_phone_comment
 * @property string $salt
 * @property null|Carbon $deleted_at
 * @property null|Carbon $created_at
 * @property null|Carbon $updated_at
 * @property null|Carbon $phone_edit_at
 * @property boolean $has_new_notifications
 */

class User extends Authenticatable
{
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use HasApiTokens;
    use SetPasswordTrait;
    use ImageRelation;
    use SoftDeletes;
    use Scopes\NameSearch;

    public const DRAFT  = 0;    // пользователь только создан
    public const ACTIVE = 1;    // пользователь активен, но не клиент AA
    public const VERIFY = 2;    // пользователь является клиентом AA

    public const GUARD = 'graph_user';
    public const TABLE_NAME = 'users';

    public const IMAGE_AVATAR_TYPE = 'avatar'; // тип в images для аватарки

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'name',
        'email',
        'password',
        'uuid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'phone_edit_at',
    ];

    protected $casts = [
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
        'new_phone' => PhoneCast::class,
        'email_verify' => 'boolean',
        'phone_verify' => 'boolean',
        'has_new_notifications' => 'boolean',
        'uuid' => UuidCast::class,
    ];

    public function isVerify(): bool
    {
        return $this->status === self::VERIFY;
    }

    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function hasNewNotification(): bool
    {
        return $this->has_new_notifications;
    }

    public static function statusList()
    {
        return [
            self::DRAFT => __('translation.user.status.draft'),
            self::ACTIVE => __('translation.user.status.active'),
            self::VERIFY => __('translation.user.status.verify')
        ];
    }

    public static function assertStatus($status, $code = ErrorsCode::BAD_REQUEST): void
    {
        if(!array_key_exists($status, self::statusList())){
            throw new \InvalidArgumentException(__('error.not valid user status', ['status' => $status]), $code);
        }
    }

    public static function checkStatus($status): bool
    {
        return array_key_exists($status, self::statusList());
    }

    public function haveNewPhone(): bool
    {
        return null != $this->new_phone;
    }

    public function hasFcmToken(): bool
    {
        return null != $this->fcm_token;
    }

    public function getFcmToken(): string
    {
        return $this->fcm_token;
    }

    public function checkDeviceId(string $deviceId): bool
    {
        return $this->device_id === $deviceId;
    }

    // Relations

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(
            Promotion::class,
            'promotion_user_relations',
            'user_id', 'promotion_id'
        );
    }

    public function locale()
    {
        return $this->hasOne(Language::class, 'slug', 'lang');
    }

    public function avatar(): MorphOne
    {
        return $this->morphOne(Image::class, 'entity')->where('type', self::IMAGE_AVATAR_TYPE);
    }

    public function fcmNotifications(): HasMany
    {
        return $this->hasMany(Fcm::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function ordersCurrent(): HasMany
    {
        return $this->hasMany(Order::class)->whereIn('status', Status::statusForCurrent());
    }

    public function ordersHistory(): HasMany
    {
        return $this->hasMany(Order::class)->whereIn('status', Status::statusForHistory());
    }

    public function emailVerifyObj()
    {
        return $this->morphOne(EmailVerify::class, 'entity');
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    public function carsGarage(): HasMany
    {
        return $this->hasMany(Car::class)
            ->where('in_garage', true);
    }

    public function carsTrashed(): HasMany
    {
        return $this->cars()->onlyTrashed();
    }

    public function selectedCar(): HasOne
    {
        return $this->hasOne(Car::class)->where('selected',true);
    }

    public function loyalties(): BelongsToMany
    {
        return $this->belongsToMany(
            Loyalty::class,
            'user_car_loyalty_pivot',
            'user_id', 'loyalty_id'
        )->where('loyalties.active', true)->withPivot(['active', 'car_id']);
    }

    public function aaResponses(): MorphMany
    {
        return $this->morphMany(AAResponse::class, 'entity');
    }

    // переопределение метода для паспорт
    public function findForPassport($username)
    {
        return self::where('id', $username)->first();
    }

    // scopes

    public function scopeUserPhoneSearch(EloquentBuilder $query, string $search): EloquentBuilder
    {
        $phone = new Phone($search, false);
        return $query->where('phone', $phone);
    }

    public function scopeUserHasNewPhone(EloquentBuilder $query, bool $search): EloquentBuilder
    {
        if($search){
            return $query->where('phone_edit_at', '!=',null);
        }

        return $query->where('phone_edit_at', '=',null);
    }

    public function scopeCarBrandId(EloquentBuilder $query, $brandId): EloquentBuilder
    {
        return $query->with('cars')
            ->whereHas('cars', function(EloquentBuilder $q) use ($brandId) {
                $q->where('brand_id', $brandId);
            });
    }

    public function scopeCarModelId(EloquentBuilder $query, $modelId): EloquentBuilder
    {
        return $query->with('cars')
            ->whereHas('cars', function(EloquentBuilder $q) use ($modelId) {
                $q->where('model_id', $modelId);
            });
    }

    public function scopeCarYear(EloquentBuilder $query, $year): EloquentBuilder
    {
        return $query->with('cars')
            ->whereHas('cars', function(EloquentBuilder $q) use ($year) {
                $q->where('year', $year);
            });
    }

    public function scopeOnlyOrderCar(EloquentBuilder $query, bool $isOrder): EloquentBuilder
    {
        return $query->with('cars')
            ->whereHas('cars', function(EloquentBuilder $q) use ($isOrder) {
                $q->where('is_order', $isOrder);
            });
    }
}
