<?php

namespace App\Models\Saas\CompanyRegistration;

use App\ModelFilters\Saas\CompanyRegistration\CompanyRegistrationFilter;
use App\Models\BaseModel;
use App\Models\Locations\State;
use App\Traits\Filterable;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * @property int id
 * @property int usdot
 * @property string|null ga_id
 * @property string first_name
 * @property string last_name
 * @property string email
 * @property string phone
 * @property bool confirmed
 * @property string password
 * @property string confirmation_hash
 * @property Carbon|null confirmed_send_at
 * @property int not_confirmed_send_count   // кол-во отправленных писем, если email не потвержденн
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see CompanyRegistration::scopeConfirmed()
 * @method static confirmed()
 *
 * @method static static|Builder query()
 *
 * @mixin Eloquent
 */
class CompanyRegistration extends BaseModel
{
    use Filterable;
    use HasFactory;
    use Notifiable;

    public const TABLE = 'company_registrations';

    protected $table = self::TABLE;

    protected $fillable = [
        'usdot',
        'ga_id',
        'mc_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'name',
        'address',
        'city',
        'state_id',
        'zip',
        'status',
        'confirmed_send_at',
        'not_confirmed_send_count',
    ];

    protected $hidden = [
        'password',
        'confirmation_hash'
    ];

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('confirmed', true);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function modelFilter(): string
    {
        return CompanyRegistrationFilter::class;
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
