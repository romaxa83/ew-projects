<?php

namespace App\Models\Companies;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Filters\Companies\ShippingAddressFilter;
use App\Models\BaseModel;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\AddressTrait;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Carbon\Carbon;
use Database\Factories\Companies\ShippingAddressFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property boolean active
 * @property string name
 * @property integer company_id
 * @property Phone phone
 * @property Phone fax
 * @property integer country_id
 * @property integer state_id
 * @property string city
 * @property string address_line_1
 * @property string|null address_line_2
 * @property string zip
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Email|null email
 * @property string|null receiving_persona
 *
 * @see ShippingAddress::country()
 * @property-read Country country
 *
 * @see ShippingAddress::state()
 * @property-read State state
 *
 * @see ShippingAddress::comapny()
 * @property-read Company company
 *
 * @method static ShippingAddressFactory factory(...$options)
 */
class ShippingAddress extends BaseModel
{
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use AddressTrait;

    public $timestamps = false;

    public const TABLE = 'company_shipping_addresses';
    protected $table = self::TABLE;

    protected $casts = [
        'phone' => PhoneCast::class,
        'fax' => PhoneCast::class,
        'email' => EmailCast::class,
        'active' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return ShippingAddressFilter::class;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo|State
    {
        return $this->belongsTo(State::class);
    }

    public function company(): BelongsTo|Company
    {
        return $this->belongsTo(Company::class);
    }
}
