<?php

namespace App\Models\Customers;

use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Enums\Customers\CustomerType;
use App\Foundations\Casts\Contact\EmailCast;
use App\Foundations\Casts\Contact\PhoneCast;
use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Contracts\HasComments;
use App\Foundations\Modules\Comment\Traits\InteractsWithComment;
use App\Foundations\Modules\Media\Contracts\HasMedia;
use App\Foundations\Modules\Media\Images\CustomerImage;
use App\Foundations\Modules\Media\Traits\InteractsWithMedia;
use App\Foundations\Traits\Filters\Filterable;
use App\Foundations\Traits\Models\CustomNotifiable;
use App\Foundations\Traits\Models\FullNameTrait;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\ModelFilters\Customers\CustomerFilter;
use App\Models\Orders\Parts\Order;
use App\Models\Tags\HasTags;
use App\Models\Tags\HasTagsTrait;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Database\Factories\Customers\CustomerFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int origin_id
 * @property CustomerType type
 * @property string first_name
 * @property string last_name
 * @property Phone phone
 * @property string phone_extension
 * @property array|null phones
 * @property Email email
 * @property string|null notes
 * @property bool from_haulk    // модель из хаулка, затягивается и используется в связке с техникой срм, которая используется для отображения в бодишопе
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property int|null sales_manager_id
 * @property bool has_ecommerce_account // есть ли аккаунт в екомерсе
 *
 * @see self::salesManager()
 * @property User|BelongsTo|null salesManager
 *
 * @see self::trucks()
 * @property Truck[]|HasMany trucks
 *
 * @see self::trailers()
 * @property Trailer[]|HasMany trailers
 *
 * @see self::partOrders()
 * @property Order[]|HasMany partOrders
 *
 * @see self::addresses()
 * @property Address[]|HasMany addresses
 *
 * @see self::taxExemption()
 * @property CustomerTaxExemption|HasOne taxExemption
 *
 * @see self::taxExemptionActive()
 * @property CustomerTaxExemption|HasOne taxExemptionActive
 *
 * @mixin Eloquent
 *
 * @method static CustomerFactory factory(...$parameters)
 */
class Customer extends BaseModel implements
    HasTags,
    HasMedia,
    HasComments
{
    use Filterable;
    use HasFactory;
    use HasTagsTrait;
    use InteractsWithMedia;
    use InteractsWithComment;
    use FullNameTrait;
    use CustomNotifiable;

    public const TABLE = 'customers';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'customer';

    public const ATTACHMENT_FIELD_NAME = 'attachment_files';

    public const ATTACHMENT_COLLECTION_NAME = 'attachments';

    /** @var array<int, string> */
    protected $fillable = [
        'origin_id',
        'first_name',
        'last_name',
        'phone',
        'phone_extension',
        'phones',
        'email',
        'notes',
        'created_at',
        'updated_at',
        'type',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'phone' => PhoneCast::class,
        'email' => EmailCast::class,
        'type' => CustomerType::class,
        'phones' => 'array',
        'from_haulk' => 'boolean',
        'has_ecommerce_account' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(CustomerFilter::class);
    }

    public function getImageClass(): string
    {
        return CustomerImage::class;
    }

    public function getAttachments(): array
    {
        return $this->getMedia(self::ATTACHMENT_COLLECTION_NAME)
            ->all();
    }

    public function salesManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_manager_id');
    }

    public function taxExemption(): HasOne
    {
        return $this->hasOne(CustomerTaxExemption::class, 'customer_id', 'id');
    }
    public function taxExemptionActive(): HasOne
    {
        return $this->taxExemption()
            ->where('status', CustomerTaxExemptionStatus::ACCEPTED);
    }

    public function trucks(): HasMany
    {
        return $this->hasMany(Truck::class, 'customer_id');
    }

    public function trailers(): HasMany
    {
        return $this->hasMany(Trailer::class, 'customer_id');
    }

    public function partOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'customer_id')
            ->orderBy('sort', 'desc');
    }

    public function defaultAddress(): null|Address
    {
        return $this->addresses->where('is_default', true)->first();
    }

    public function hasRelatedEntities(): bool
    {
        return $this->trucks()->exists() || $this->trailers()->exists();
    }

    public function hasRelatedPartOrders(): bool
    {
        return $this->partOrders()->exists();
    }

    public function hasECommTag(): bool
    {
        return $this
            ->tags()
            ->where('name', Tag::ECOM_NAME_TAG)
            ->exists();
    }

    public function hasECommAccount(): bool
    {
        return $this->has_ecommerce_account;
    }

    public function canAddNew(): bool
    {
        return !($this->addresses->count() >= Address::MAX_COUNT);
    }
}
