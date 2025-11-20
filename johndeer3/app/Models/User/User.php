<?php

namespace App\Models\User;

use App\ModelFilters\User\UserFilter;
use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\Notification\FcmNotification;
use App\Models\Report\Report;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\User\User
 *
 * @property int $id
 * @property string|null $login
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $password
 * @property integer $status
 * @property integer|null $jd_id
 * @property integer|null $dealer_id
 * @property integer|null $nationality_id
 * @property string|null $email_verified_at
 * @property string|null $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property string $lang
 * @property string|null $fcm_token
 * @property string|null $ios_link
 *
 * @property-read  Profile $profile
 * @property-read  Nationality $country
 * @property-read  Dealer $dealer
 * @property-read  EquipmentGroup[]|Collection $egs
 * @property-read  FcmNotification[]|Collection $fcm_notifications
 *
 * @method static Builder|self query()
 * @method static Builder|self Ps()
 * @method static Builder|self notAdmin()
 */

class User extends Authenticatable
{
    use Notifiable;
    use Filterable;
    use HasApiTokens;
    use HasFactory;

    const DEFAULT_PER_PAGE = 10;
    const DEFAULT_PASSWORD = 'password';

    const STATUS_ACTIVE = 1;

    protected $fillable = [
        'login',
        'email',
        'password',
        'phone',
        'dealer_id',
        'ios_link',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(UserFilter::class);
    }

    public static function generateRandomPassword()
    {
        return env('RANDOM_PASSWORD') ? Str::random(8) : self::DEFAULT_PASSWORD;
    }

    public function isActive(): bool
    {
        return $this->status;
    }

    public function isAdmin()
    {
        return $this->getRole() === Role::ROLE_ADMIN;
    }

    public function isPS()
    {
        return $this->getRole() === Role::ROLE_PS;
    }

    public function isPSS()
    {
        return $this->getRole() === Role::ROLE_PSS;
    }

    public function isSM()
    {
        return $this->getRole() === Role::ROLE_SM;
    }

    public function isTM()
    {
        return $this->getRole() === Role::ROLE_TM;
    }

    public function isTMD()
    {
        return $this->getRole() === Role::ROLE_TMD;
    }

    public function getRoleName()
    {
        return $this->roles[0]->current[0]->text ?? null;
    }

    public function getRole()
    {
        return $this->roles[0]->role;
    }

    // relation

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function reports() : HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function dealers(): BelongsToMany
    {
        return $this->belongsToMany(
            Dealer::class,
            'dealer_user',
            'user_id', 'dealer_id'
        );
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'nationality_id', 'id');
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function egs(): BelongsToMany
    {
        return $this->belongsToMany(
            EquipmentGroup::class,
            'user_eg_relation',
            'user_id', 'eg_id'
        );
    }

    public function fcm_notifications(): MorphMany
    {
        return $this->morphMany(FcmNotification::class, 'entity');
    }

    public function fullName()
    {
        $this->load(['profile']);
        return ucfirst($this->profile->first_name) . ' ' . ucfirst($this->profile->last_name);
    }

    // scope

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopePs(Builder $query)
    {
        return $query->whereHas('roles', function(Builder $query){
            $query->where('role', Role::ROLE_PS);
        });
    }

    // переопределение метода для паспорт
    public function findForPassport($username)
    {
        return self::where('email', $username)->first();
    }
}
