<?php

namespace App\Models\Commercial;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Contracts\Members\HasCommercialProjects;
use App\Contracts\Utilities\Dispatchable;
use App\Enums\Commercial\CommercialProjectStatusEnum;
use App\Enums\Commercial\Commissioning\ProtocolType;
use App\Enums\Formats\DatetimeEnum;
use App\Filters\Commercial\CommercialProjectFilter;
use App\Models\BaseModel;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Warranty\WarrantyRegistration;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Carbon\Carbon;
use Database\Factories\Commercial\CommercialProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int id
 * @property string|null guid
 * @property CommercialProjectStatusEnum status
 * @property string name
 * @property string address_line_1
 * @property string|null address_line_2
 * @property string city
 * @property int|null state_id
 * @property int|null country_id
 * @property string zip
 * @property string first_name
 * @property string last_name
 * @property Phone phone
 * @property Email email
 * @property string company_name
 * @property string company_address
 * @property string description
 * @property Carbon estimate_start_date
 * @property Carbon estimate_end_date
 * @property Carbon|null start_commissioning_date
 * @property Carbon|null end_commissioning_date
 * @property Carbon|null start_pre_commissioning_date
 * @property Carbon|null end_pre_commissioning_date
 * @property Carbon|null request_until      // до которого момента у техника есть время сделать запрос на получение кредов к rdp
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see CommercialProject::state()
 * @property-read State state
 *
 * @see CommercialProject::country()
 * @property-read Country country
 *
 * @property-read HasCommercialProjects member
 * @property-read Collection|CommercialQuote[] quotes
 *
 * @see CommercialProject::projectProtocols()
 * @property-read Collection|ProjectProtocol[] projectProtocols
 *
 * @see CommercialProject::projectProtocolsPreCommissioning()
 * @property-read Collection|ProjectProtocol[] projectProtocolsPreCommissioning
 *
 * @see CommercialProject::projectProtocolsCommissioning()
 * @property-read Collection|ProjectProtocol[] projectProtocolsCommissioning
 *
 * @see CommercialProject::units()
 * @property-read Collection|CommercialProjectUnit[] units
 *
 * @see CommercialProject::warranty()
 * @property-read WarrantyRegistration|null warranty
 *
 * @see CommercialProject::additions()
 * @property-read CommercialProjectAddition|null additions
 *
 * @see CommercialProject::scopeRequestExpired()
 * @method Builder|static requestExpired()
 *
 * @see CommercialProject::scopePending()
 * @method Builder|static pending()
 *
 * @method static CommercialProjectFactory factory(...$parameters)
 */
class CommercialProject extends BaseModel implements Dispatchable
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'commercial_projects';

    protected $table = self::TABLE;

    protected $casts = [
        'status' => CommercialProjectStatusEnum::class,
        'phone' => PhoneCast::class,
        'email' => EmailCast::class,
        'estimate_start_date' => DatetimeEnum::DEFAULT,
        'estimate_end_date' => DatetimeEnum::DEFAULT,
        'request_until' => DatetimeEnum::DEFAULT,
        'start_pre_commissioning_date' => DatetimeEnum::DEFAULT,
        'end_pre_commissioning_date' => DatetimeEnum::DEFAULT,
        'start_commissioning_date' => DatetimeEnum::DEFAULT,
        'end_commissioning_date' => DatetimeEnum::DEFAULT,
    ];

    protected $dates = [
        'start_pre_commissioning_date',
        'end_pre_commissioning_date',
        'start_commissioning_date',
        'end_commissioning_date',
    ];

    protected $fillable = [
        'guid',
    ];

    public function modelFilter(): string
    {
        return CommercialProjectFilter::class;
    }

    public function isStartPreCommissioning(): bool
    {
        return $this->start_pre_commissioning_date !== null;
    }

    public function isStartCommissioning(): bool
    {
        return $this->start_commissioning_date !== null;
    }

    public function isEndCommissioning(): bool
    {
        return $this->end_commissioning_date !== null;
    }

    /**
     * Returns the project that has the same address, but created earlier than this one
     */
    public function previous(): BelongsTo|self
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Returns the project that has the same address, but created later than this one
     */
    public function next(): HasOne|self
    {
        return $this->hasOne(self::class, 'parent_id');
    }

    public function member(): MorphTo|HasCommercialProjects
    {
        return $this->morphTo();
    }

    public function state(): BelongsTo|State
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo|Country
    {
        return $this->belongsTo(Country::class);
    }

    public function additions(): HasOne|CommercialProjectAddition
    {
        return $this->hasOne(CommercialProjectAddition::class);
    }

    public function warranty(): HasOne|WarrantyRegistration
    {
        return $this->hasOne(WarrantyRegistration::class);
    }

    public function credentialsRequests(): HasMany|CredentialsRequest
    {
        return $this->hasMany(CredentialsRequest::class);
    }

    public function quotes(): HasMany|CommercialQuote
    {
        return $this->hasMany(CommercialQuote::class);
    }

    public function units(): HasMany|CommercialProjectUnit
    {
        return $this->hasMany(CommercialProjectUnit::class);
    }

    public function projectProtocols(): HasMany|ProjectProtocol
    {
        return $this->hasMany(ProjectProtocol::class, 'project_id','id')->latest('sort');
    }

    public function projectProtocolsPreCommissioning(): HasMany|ProjectProtocol
    {
        return $this->projectProtocols()->whereHas('protocol', fn(Builder $b) => $b->where('type', ProtocolType::PRE_COMMISSIONING));
    }

    public function projectProtocolsCommissioning(): HasMany|ProjectProtocol
    {
        return $this->projectProtocols()->whereHas('protocol', fn(Builder $b) => $b->where('type', ProtocolType::COMMISSIONING));
    }

    public function scopeRequestExpired(Builder|self $builder): void
    {
        $builder->where('request_until', '<=', now());
    }

    public function scopePending(Builder|self $builder): void
    {
        $builder->where('status', CommercialProjectStatusEnum::PENDING);
    }

    // example - 7 Levis Circle Str. 328571 , Los Angeles, California, 19804
    public function getFullAddressAttribute(): string
    {
        $address = $this->address_line_1;
        if($this->address_line_2) {
            $address .= ', ' . $this->address_line_2;
        }
        $address .= ', ' . $this->city . ', ' . $this->state->short_name . ', ' . $this->zip;

        return  $address;
    }
}
