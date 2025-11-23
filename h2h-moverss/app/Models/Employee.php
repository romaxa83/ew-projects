<?php

namespace App\Models;

use App\Enums\Employee\SalesTeamEnum;
use App\Models\CashRegistry\CashRegistry;
use App\Utils\UpdateRelationsTrait;
use App\Models\Employee\{Busy, BusyWeekDay, Email, Messenger, Notes, PbxData, Phone, Rate, SalesPlan};
use Illuminate\Database\Eloquent\{
    Builder,
    Factories\HasFactory,
    Model,
    Relations\HasMany,
    Relations\HasOne,
    SoftDeletes,
    Collection
};
use App\User;
use Database\Factories\Employees\EmployeeFactory;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Employee
 *
 * @property int $id
 * @property int $active
 * @property array|null $division_ids
 * @property string|null $name
 * @property string|null $l_name
 * @property string|null $address
 * @property string|null $birthday
 * @property string|null $pay_type
 * @property string|null $signature
 * @property int|null $auth_user_id
 * @property string|null $pbx_ext
 * @property int $pbx_show_webrtc
 * @property string|null $driver_start_of_work
 * @property string|null $driver_notes
 * @property int|null partner_id
 * @property bool is_partner_head // яв. ли главным среди сотрудников партнера
 * @property bool zadarma_sip_status        // статус сип аккаунта в zadarma
 * @property bool ringostat_sip_status      // статус сип аккаунта в рингостат
 * @property int|null ringostat_id          // id в рингостат
 * @property int|null ringostat_call_rec_id // id записи если сотрудник в текущий момент на созвоне
 * @property int|null zadarma_call_rec_id   // id записи если сотрудник в текущий момент на созвоне
 * @property array|null ringostat_miscs
 * @property string|null callers_number
 * @property SalesTeamEnum|null sales_team
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read mixed $full_name
 * @property-read Collection|Busy[] $busyDates
 * @property-read int|null $busy_dates_count
 * @property-read BusyWeekDay|null $busyWeeksDays
 * @property-read Collection|Email[] $emails
 * @property-read int|null $emails_count
 * @property-read Collection|Messenger[] $messengers
 * @property-read int|null $messengers_count
 * @property-read Collection|Notes[] $notes
 * @property-read int|null $notes_count
 * @property-read Collection|Phone[] $phones
 * @property-read Collection|SalesPlan[] salesPlans
 * @property-read int|null $phones_count
 * @property-read User|null $user
 * @property-read int|null $audits_count
 * @property-read int|null $pbxdata_count
 * @property-read Collection|PbxData[] $pbxdata
 * @method static Builder|Employee activeDispatch($assignedWorks)
 * @method static Builder|Employee newModelQuery()
 * @method static Builder|Employee newQuery()
 * @method static \Illuminate\Database\Query\Builder|Employee onlyTrashed()
 * @method static Builder|Employee query()
 * @method static Builder|Employee record()
 * @method static Builder|Employee whereActive($value)
 * @method static Builder|Employee whereAddress($value)
 * @method static Builder|Employee whereAuthUserId($value)
 * @method static Builder|Employee whereBirthday($value)
 * @method static Builder|Employee whereCreatedAt($value)
 * @method static Builder|Employee whereDeletedAt($value)
 * @method static Builder|Employee whereId($value)
 * @method static Builder|Employee whereLName($value)
 * @method static Builder|Employee whereName($value)
 * @method static Builder|Employee wherePayType($value)
 * @method static Builder|Employee whereSignature($value)
 * @method static Builder|Employee whereUpdatedAt($value)
 * @method static Builder|Employee wherePbxExt($value)
 * @method static Builder|Employee wherePbxShowWebrtc($value)
 * @method static \Illuminate\Database\Query\Builder|Employee withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Employee withoutTrashed()
 * @method static Builder|Employee whereDivisionIds($value)
 * @property-read Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static Builder|Employee whereDriverNotes($value)
 * @method static Builder|Employee whereDriverStartOfWork($value)
 * @method static EmployeeFactory factory(...$parameters)
 * @method static Builder|Employee wherePartnerId($value)
 *
 * @see self::cashRegistry()
 * @property CashRegistry|HasOne cashRegistry
 *
 * @mixin \Eloquent
 */
class Employee extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UpdateRelationsTrait;
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'employees';
    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'l_name',
        'address',
        'birthday',
        'pay_type',
        'signature',
        'pbx_ext',
        'pbx_show_webrtc',
        'division_ids',
        'driver_start_of_work',
        'driver_notes',
        'active',
        'partner_id',
        'is_partner_head',
        'zadarma_sip_status',
        'ringostat_sip_status',
        'ringostat_id',
        'ringostat_call_rec_id',
        'zadarma_call_rec_id',
        'ringostat_miscs',
        'callers_number',
        'sales_team',
    ];
    protected $dates = ['deleted_at'];

    protected $casts = [
        'division_ids' => 'json',
        'ringostat_miscs' => 'json',
        'ringostat_sip_status' => 'boolean',
        'zadarma_sip_status' => 'boolean',
        'is_partner_head' => 'boolean',
        'sales_team' => SalesTeamEnum::class,
    ];

    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }

    public function isActive(): bool
    {
        return $this->active == 1;
    }

    public function isPartner(): bool
    {
        return !is_null($this->partner_id);
    }

    public function isPartnerHead(): bool
    {
        return $this->is_partner_head;
    }

    public function getPartnerHead(): Employee
    {
        return Employee::query()
            ->with('user')
            ->where('partner_id', $this->partner_id)
            ->where('is_partner_head', true)
            ->first();
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'auth_user_id');
    }

    public function busyDates(): HasMany
    {
        return $this->hasMany(Busy::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function busyWeeksDays(): HasOne
    {
        return $this->hasOne(BusyWeekDay::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function salesPlans(): HasMany
    {
        return $this->hasMany(SalesPlan::class);
    }

    public function messengers(): HasMany
    {
        return $this->hasMany(Messenger::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Notes::class);
    }

    public function pbxdata(): HasMany
    {
        return $this->hasMany(PbxData::class);
    }

    public function cashRegistry(): HasOne
    {
        return $this->hasOne(CashRegistry::class, 'employee_id');
    }

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    public function scopeRecord($query)
    {
        return $query
            ->with([
                'user:id,active,email',
                'user.roles',
                'notes',
                'busyDates',
                'busyWeeksDays',
                'pbxData',
                'phones' => function ($q) {
                    return $q
                        ->select(['id', 'employee_id', 'type_id', 'is_primary', 'value'])
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
                'emails' => function ($q) {
                    return $q
                        ->select(['id', 'employee_id', 'is_primary', 'value'])
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
                'messengers:id,employee_id,type_id,value',
            ]);
    }

    /**
     * Данные для выборки сотрудников для диспатча.
     * @param $q
     * @param $assignedWorks
     * @return mixed
     */
    public function scopeActiveDispatch($q, $assignedWorks)
    {
        $ids = $assignedWorks->pluck('dispatchEmployees.*.employer_id')->filter()->flatten()->all();

        return $q
            ->where(function ($q) use ($ids) {
                $q->whereActive(1)
                    ->whereJsonContains('division_ids', request()->session()->get('division.id'));
                if ($ids) {
                    $q->orWhereIn('id', $ids);
                }
            })
            ->with([
                'user:id,active,email',
                'user.roles',
            ])
            ->whereHas('user.roles', function ($q) {
                return $q->whereNotIn('users_roles.id', [1, 5, 7]); // Not: Admin, Manager, Test team
            })
            ->select(['id', 'name', 'l_name', 'active', 'auth_user_id'])
            ->orderBy('name')
            ->orderBy('l_name');
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->l_name;
    }

    public function isOnline(): bool
    {
        return $this->ringostat_sip_status || $this->zadarma_sip_status;
    }

    public function isOnlineProvider(): ?string
    {
        if($this->ringostat_sip_status){
            return 'ringostat';
        }
        if($this->zadarma_sip_status){
            return 'zadarma';
        }

        return null;
    }

}
