<?php

namespace App\Models\Companies;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Enums\Companies\ContactType;
use App\Models\BaseModel;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Traits\HasFactory;
use App\Traits\Model\AddressTrait;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Database\Factories\Companies\ContactFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property ContactType type
 * @property integer company_id
 * @property string name
 * @property Email email
 * @property Phone phone
 * @property integer state_id
 * @property integer country_id
 * @property string city
 * @property string address_line_1
 * @property string|null address_line_2
 * @property string zip
 * @property string|null po_box
 *
 * @see Contact::country()
 * @property-read Country country
 *
 * @see Contact::state()
 * @property-read State state
 *
 * @method static ContactFactory factory(...$options)
 */
class Contact extends BaseModel
{
    use HasFactory;
    use AddressTrait;

    public $timestamps = false;

    public const TABLE = 'company_contacts';
    protected $table = self::TABLE;

    protected $casts = [
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
        'type' => ContactType::class,
    ];

    public function state(): BelongsTo|State
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
