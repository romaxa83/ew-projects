<?php

namespace WezomCms\Users\Models;

use Greabock\Tentacles\EloquentTentacle;
use http\Exception\InvalidArgumentException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use WezomCms\Core\Api\ErrorCode;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Promotions\Models\Promotions;
use WezomCms\Users\Types\LoyaltyType;
use WezomCms\Users\Types\UserCarStatus;
use WezomCms\Users\Types\UserStatus;
use WezomCms\Users\UseCase\UserStatuses;

/**
 * \WezomCms\Users\Models\User
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $patronymic
 * @property string $phone
 * @property string|null $email
 * @property string $password
 * @property int $status
 * @property string|null $fcm_token
 * @property bool $phone_verified
 * @property string|null $phone_verify_token
 * @property string|null $niko_status
 * @property string $lang
 * @property integer $loyalty_type
 * @property integer $loyalty_level
 * @property string|null $change_phone_comment
 * @property string|null $device_id
 * @property \Illuminate\Support\Carbon|null $phone_verify_token_expire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $full_name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
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
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User whereRegisteredThrough($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereActive($value)
 * @method static Builder|User whereSurname($value)
 * @method static Builder|User whereTemporaryCode($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static Builder|User whereManagerId($value)
 * @method static Builder|User active()
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use Filterable;
    use Notifiable;
    use EloquentTentacle;

    public const DEFAULT_PASSWORD = 'password';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'phone_verified' => 'bool',
        'phone_verify_token_expire' => 'datetime'
    ];

    public function isVerify(): bool
    {
        return UserStatus::isVerify($this->status);
    }

    public function checkDeviceId(string $deviceId): bool
    {
        return $this->device_id === $deviceId;
    }

    public function assertDeviceId(string $deviceId): void
    {
        if(!$this->checkDeviceId($deviceId)){
            throw new \InvalidArgumentException(__('cms-users::site.message.device id wrong'), ErrorCode::DEVICE_ID_INCORRECT);
        }
    }

    public function hasLoyaltyType()
    {
        return LoyaltyType::hasType($this->loyalty_type);
    }

    // рендер статусов в админке (в виде бейджев)
    public function getStatusesAttribute()
    {
        $status = new UserStatuses($this->load(['loyalty']));

        return $status->forAdmin();
    }

    public function cars()
    {
        return $this->hasMany(Car::class)->where('status' , UserCarStatus::ACTIVE);
    }

    public function loyalty()
    {
        return $this->hasOne(UserLoyalty::class);
    }

    public function carsDeleted()
    {
        return $this->hasMany(Car::class)->where('status' , UserCarStatus::DELETED);
    }

    public function carsFromOrder()
    {
        return $this->hasMany(Car::class)->where('status' , UserCarStatus::FROM_ORDER);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function promotions()
    {
        return $this->belongsToMany(
            Promotions::class,
            'promotions_user_relation',
            'user_id', 'promotions_id'
        );
    }

    public function countCar()
    {
        return $this->cars->count();
    }

    /**
     * @return string
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // переопределение метода для паспорт
    public function findForPassport($username)
    {
        return self::where('id', $username)->first();
    }
}
