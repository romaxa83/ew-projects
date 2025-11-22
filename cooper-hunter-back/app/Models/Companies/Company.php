<?php

namespace App\Models\Companies;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Contracts\Media\HasMedia;
use App\Contracts\Payment\PaymentModel;
use App\Contracts\Utilities\Dispatchable;
use App\Enums\Companies\CompanyStatus;
use App\Enums\Companies\CompanyType;
use App\Enums\Companies\ContactType;
use App\Filters\Companies\CompanyFilter;
use App\Models\BaseModel;
use App\Models\Dealers\Dealer;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Payments\PaymentCard;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\AddressTrait;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Payment\InteractsWithPaymentCard;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Database\Factories\Companies\CompanyFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property CompanyType type
 * @property CompanyStatus status
 * @property int|null corporation_id
 * @property string|null guid
 * @property string|null code
 * @property array|null terms
 * @property string business_name
 * @property Email email
 * @property Phone|null phone
 * @property int country_id
 * @property int state_id
 * @property string city
 * @property string address_line_1
 * @property string|null address_line_2
 * @property string|null po_box
 * @property string zip
 * @property Phone|null fax
 * @property string|null taxpayer_id
 * @property string|null tax
 * @property string|null websites
 * @property string|null marketplaces
 * @property string|null trade_names
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see Company::country()
 * @property-read Country country
 *
 * @see Company::state()
 * @property-read State state
 *
 * @see Company::corporation()
 * @property-read Corporation|null corporation
 *
 * @see Company::shippingAddresses()
 * @see Company::shippingAddressesActive()
 * @property-read ShippingAddress|Collection shippingAddresses
 * @property-read ShippingAddress|Collection shippingAddressesActive
 *
 * @see Company::prices()
 * @property-read Price|Collection prices
 *
 * @see Company::dealers()
 * @property-read Dealer|Collection dealers
 *
 * @see Company::mainDealer()
 * @property-read Dealer mainDealer
 *
 * @see Company::manager()
 * @property-read Manager manager
 *
 * @see Company::commercialManager()
 * @property-read CommercialManager commercialManager
 *
 * @see Company::contacts()
 * @property-read Contact contacts
 *
 * @see Company::cards()
 * @property-read PaymentCard|Collection cards
 *
 * @see Company::getTermNamesAttribute()
 * @property-read string|null term_names
 *
 * @method static CompanyFactory factory(...$options)
 */
class Company extends BaseModel implements
    HasMedia,
    Dispatchable,
    PaymentModel
{
    use HasFactory;
    use Filterable;
    use InteractsWithMedia;
    use InteractsWithPaymentCard;
    use AddressTrait;

    public const MEDIA_COLLECTION_NAME = 'companies';
    public const MORPH_NAME = 'company';

    public const TABLE = 'companies';
    protected $table = self::TABLE;

    protected $fillable = [
        'guid',
    ];

    protected $casts = [
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
        'fax' => PhoneCast::class,
        'status' => CompanyStatus::class,
        'type' => CompanyType::class,
        'websites' => 'array',
        'marketplaces' => 'array',
        'trade_names' => 'array',
        'terms' => 'array',
    ];

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(array_merge(
                $this->mimeImage(),
                $this->mimeExcel(),
                $this->mimePdf(),
            ));
    }

    public function modelFilter(): string
    {
        return CompanyFilter::class;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo|State
    {
        return $this->belongsTo(State::class);
    }

    public function corporation(): BelongsTo|Corporation
    {
        return $this->belongsTo(Corporation::class);
    }

    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class);
    }

    public function shippingAddressesActive(): HasMany
    {
        return $this->shippingAddresses()->where('active', true);
    }

    public function manager(): HasOne
    {
        return $this->hasOne(Manager::class);
    }

    public function commercialManager(): HasOne
    {
        return $this->hasOne(CommercialManager::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function dealers(): HasMany
    {
        return $this->hasMany(Dealer::class);
    }

    public function mainDealer(): HasOne
    {
        return $this->hasOne(Dealer::class)
            ->where('is_main_company', true);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function contactAccount(): HasOne
    {
        return $this->hasOne(Contact::class)->where('type', ContactType::ACCOUNT);
    }
    public function contactOrder(): HasOne
    {
        return $this->hasOne(Contact::class)->where('type', ContactType::ORDER);
    }

    public function getTermNamesAttribute(): ?string
    {
        $str = null;
        if($this->terms){
            foreach ($this->terms as $term){
                $str .= data_get($term, 'name') . ', ';
            }

            $str = substr($str, 0, -2);
        }

        return $str;
    }
}
