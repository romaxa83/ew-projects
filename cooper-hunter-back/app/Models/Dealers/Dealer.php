<?php

namespace App\Models\Dealers;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Members\Member;
use App\Contracts\Payment\PaymentModel;
use App\Contracts\Utilities\Dispatchable;
use App\Filters\Dealers\DealerFilter;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\BaseAuthenticatable;
use App\Models\Companies\Company;
use App\Models\Companies\ShippingAddress;
use App\Models\Fcm\FcmToken;
use App\Models\Languageable;
use App\Models\ListPermission;
use App\Models\Payments\PaymentCard;
use App\Permissions\Dealers\DealerDeletePermission;
use App\Permissions\Dealers\DealerUpdatePermission;
use App\Traits\AddSelectTrait;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Localization\SetLanguageTrait;
use App\Traits\Payment\InteractsWithPaymentCard;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Core\WebSocket\Contracts\Subscribable;
use Database\Factories\Dealers\DealerFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string|null guid
 * @property int company_id
 * @property Email email
 * @property Phone|null phone
 * @property string|null first_name
 * @property string|null last_name
 * @property boolean is_main            // гл. дилер в рамках корпорации (несколько компаний), устанавливается в админке
 * @property boolean is_main_company    // гл. дилер в рамках компании, устанавливается автоматически, при регистрации первого дилера в компании
 * @property string lang
 * @property string|null email_verification_code
 * @property string|null remember_token
 * @property Carbon|null email_verified_at
 * @property Carbon|null deleted_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see Dealer::company()
 * @property-read Company company
 *
 * @see Dealer::cards()
 * @property-read PaymentCard|Collection cards
 *
 * @see Dealer::shippingAddresses()
 * @property-read ShippingAddress|Collection shippingAddresses
 *
 * @method static DealerFactory factory(...$options)
 */
class Dealer extends BaseAuthenticatable implements
    Languageable,
    ListPermission,
    Subscribable,
    Member,
    PaymentModel,
    AlertModel,
    Dispatchable
{
    use HasFactory;
    use HasRoles;
    use Filterable;
    use Notifiable;
    use SetPasswordTrait;
    use SetLanguageTrait;
    use LanguageRelation;
    use AddSelectTrait;
    use DefaultListPermissionTrait;
    use InteractsWithPaymentCard;

    public const GUARD = 'graph_dealer';
    public const MORPH_NAME = 'dealer';
    public const MEDIA_COLLECTION_NAME = 'dealers';

    public const TABLE = 'dealers';
    protected $table = self::TABLE;

    public const ALLOWED_SORTING_FIELDS = [
        'name',
        'email',
        'roles',
        'created_at',
    ];
    public const ALLOWED_SORTING_FIELDS_RELATIONS = [
        'roles' => 'roles.translation.title',
    ];

    protected static array $eagerLoadingFields = [
        'language' => 'lang',
    ];

    protected $fillable = [
        'guid',
        'email',
        'password',
        'lang',
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
        'is_main' => 'boolean',
        'is_main_company' => 'boolean',
    ];

    public function getGuardName(): string
    {
        return self::GUARD;
    }

    public function modelFilter(): string
    {
        return DealerFilter::class;
    }

    public function isMain(): bool
    {
        return $this->is_main;
    }

    public function isMainCompany(): bool
    {
        return $this->is_main_company;
    }

    public function isSimple(): bool
    {
        return !$this->is_main && !$this->is_main_company;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function shippingAddresses(): BelongsToMany
    {
        return $this->belongsToMany(
            ShippingAddress::class,
            DealerShippingAddressPivot::TABLE
        );
    }

    public function alerts(): MorphToMany|Collection
    {
        return $this->morphToMany(
            Alert::class,
            'recipient',
            AlertRecipient::TABLE
        )
            ->withPivot('is_read');
    }

    public function getUniqId(): string
    {
        return $this->getMorphClass() . '.' . $this->getKey();
    }

    public function fcmTokens(): MorphMany
    {
        return $this->morphMany(FcmToken::class, 'member');
    }

    public function getMorphType(): string
    {
        return self::MORPH_NAME;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        $fullName = sprintf('%s %s', $this->first_name, $this->last_name);

        return str_replace(' ', ' ', $fullName);
    }

    public function getLangSlug(): ?string
    {
        return $this->lang;
    }

    public function canBeDeleted(): bool
    {
        return true;
    }

    public function canBeUpdated(): bool
    {
        return true;
    }

    public function getDeletePermissionKey(): string
    {
        return DealerDeletePermission::KEY;
    }

    public function getUpdatePermissionKey(): string
    {
        return DealerUpdatePermission::KEY;
    }

    public function getPhoneString(): string
    {
        return (string)$this->phone;
    }

    public function phoneVerified(): bool
    {
        return (bool)$this->phone_verified_at;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getEmailString(): string
    {
        return (string)$this->getEmail();
    }

    public function isEmailVerified(): bool
    {
        return (bool)$this->email_verified_at;
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->email_verification_code;
    }

    public function getShippingAddresses()
    {
        if($this->isMain()){
            $this->load('company.corporation.companies.shippingAddresses');
            $addr = collect();
            $this->company
                ?->corporation
                ->companies
                ->each(function(Company $company) use(&$addr) {
                    foreach ($company->shippingAddresses as $item){
                        $addr->push($item);
                    }
                });

            return $addr;
        }
        if($this->isMainCompany()){
            return $this->company->shippingAddresses;
        }

        return $this->shippingAddresses;
    }
}
