<?php

namespace App\Models\Orders;

use App\Collections\Models\Orders\MediaCollection;
use App\Entities\Contacts\ContactEntity;
use App\Entities\Contacts\TimeEntity;
use App\Models\DiffableInterface as DiffableInterface;
use App\Models\Files\Media;
use App\Models\Files\OrderImage;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\Locations\State;
use App\Models\Orders\Traits\OrderLoadScopesTrait;
use App\Models\Orders\Traits\OrderSetterTrait;
use App\Models\Payrolls\Payroll;
use App\Models\Saas\Company\Company;
use App\Models\SendDocsDelay;
use App\Models\Tags\HasTags;
use App\Models\Tags\HasTagsTrait;
use App\Models\Users\User;
use App\Scopes\CompanyScope;
use App\Services\Push\PushService;
use App\Traits\Diffable;
use App\Traits\SetCompanyId;
use App\ValueObjects\Orders\OverdueData;
use Database\Factories\Orders\OrderFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia\HasMedia;

/**
 * @property int|null id
 * @property int|null carrier_id
 * @property int|null broker_id
 * @property int|null driver_id
 * @property int|null driver_pickup_id
 * @property int|null driver_delivery_id
 * @property int|null dispatcher_id
 * @property int|null inspection_type
 * @property string load_id
 * @property int status
 * @property int|mixed user_id
 *
 * @property bool is_billed
 * @property bool has_pickup_inspection
 * @property bool has_pickup_signature
 * @property bool has_delivery_inspection
 * @property bool has_delivery_signature
 * @property bool delivery_customer_not_available
 * @property bool delivery_customer_refused_to_sign
 * @property bool pickup_customer_not_available
 * @property bool pickup_customer_refused_to_sign
 * @property bool has_review
 * @property bool need_review
 * @property bool seen_by_driver
 *
 * @property string instructions
 * @property string dispatch_instructions
 * @property string pickup_customer_full_name
 * @property string delivery_customer_full_name
 * @property string pickup_buyer_name_number
 * @property string delivery_buyer_number
 *
 * @property string pickup_full_name
 * @property string delivery_full_name
 * @property string shipper_full_name
 *
 * @property int pickup_date
 * @property int delivery_date_actual
 * @property Carbon|null delivery_date_actual_tz
 * @property array delivery_date_data
 * @property bool is_manual_change_to_delivery
 * @property int delivery_date
 * @property int pickup_date_actual
 * @property Carbon|null pickup_date_actual_tz
 * @property array pickup_date_data
 * @property bool is_manual_change_to_pickup
 *
 * @property array pickup_contact
 * @property array delivery_contact
 * @property array shipper_contact
 *
 * @property array pickup_time
 * @property array delivery_time
 *
 * @property array distance_data
 *
 * @property string $pickup_comment
 * @property string $delivery_comment
 * @property string $shipper_comment
 *
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property null|string public_token
 *
 * @see Order::scopeAddCalculatedStatus()
 * @property null|string calculated_status
 *
 * @see Order::expenses()
 * @property Collection|Expense[] expenses
 *
 * @see Order::bonuses()
 * @property Collection|Bonus[] bonuses
 *
 * @see Order::payment()
 * @property Payment|null payment
 *
 * @see Order::paymentStages()
 * @property PaymentStage[]|Collection paymentStages
 *
 * @see Order::vehicles()
 * @property Vehicle[]|Collection vehicles
 *
 * @property Media[]|Collection media
 *
 * @see Order::comments()
 * @property OrderComment[]|Collection comments
 *
 * @see Order::user()
 * @property User user
 *
 * @see Order::driver()
 * @property User driver
 *
 * @see Order::driverPickup()
 * @property User driverPickup
 *
 * @see Order::driverDelivery()
 * @property User driverDelivery
 *
 * @see Order::dispatcher()
 * @property User dispatcher
 *
 * @see static::company()
 * @property Company|BelongsTo company
 *
 * @see Order::scopeOffers()
 * @method static self|Builder offers()
 *
 * @method static self|Builder filter(array $attributes = [])
 * @method static self|Builder query()
 * @method static self|Builder where($column, $operator = null, $value = null)
 * @method static self|Builder first()
 * @method static self firstOrFail()
 * @method static self[]|Collection get()
 *
 * @method static OrderFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class Order extends Model implements HasMedia, DiffableInterface, HasTags
{
    use Filterable;
    use HasMediaTrait;
    use SoftDeletes;
    use SetCompanyId;
    use Diffable;
    use HasTagsTrait;
    use HasFactory;
    use OrderSetterTrait;
    use OrderLoadScopesTrait;

    public const LOCATION_PICKUP = 'pickup';
    public const LOCATION_DELIVERY = 'delivery';
    public const INVOICE_SENT = 'invoice-sent';

    public const LOCATIONS = [
        self::LOCATION_PICKUP => 'Pickup',
        self::LOCATION_DELIVERY => 'Delivery',
    ];

    public const TERMS_BEGINS = [
        self::LOCATION_PICKUP => 'Pickup',
        self::LOCATION_DELIVERY => 'Delivery',
        self::INVOICE_SENT => 'Day of invoice sent',
    ];

    public const MOBILE_TAB_IN_WORK = 'in_work';
    public const MOBILE_TAB_PLAN = 'plan';
    public const MOBILE_TAB_HISTORY = 'history';

    public const CUSTOMER_SIGNATURE_FIELD_NAME = 'customer_signature';
    public const DRIVER_SIGNATURE_FIELD_NAME = 'driver_signature';
    public const PICKUP_CUSTOMER_SIGNATURE_COLLECTION_NAME = 'pickup_customer_signature';
    public const PICKUP_DRIVER_SIGNATURE_COLLECTION_NAME = 'pickup_driver_signature';
    public const PICKUP_DRIVER_INSPECTION_BOL_COLLECTION_NAME = 'pickup_driver_inspection_bol';
    public const DELIVERY_CUSTOMER_SIGNATURE_COLLECTION_NAME = 'delivery_customer_signature';
    public const DELIVERY_DRIVER_SIGNATURE_COLLECTION_NAME = 'delivery_driver_signature';
    public const DELIVERY_DRIVER_INSPECTION_BOL_COLLECTION_NAME = 'delivery_driver_inspection_bol';

    public const DRIVER_PAYMENT_FIELD_NAME = 'driver_payment_check_photo';
    public const DRIVER_PAYMENT_COLLECTION_NAME = 'driver_payment_check_photo';

    public const DRIVER_DOCUMENTS_FIELD_NAME = 'document';
    public const DRIVER_DOCUMENTS_COLLECTION_NAME = 'driver_documents';
    public const DRIVER_PHOTOS_FIELD_NAME = 'photo';
    public const DRIVER_PHOTOS_COLLECTION_NAME = 'driver_photos';

    public const ATTACHMENT_FIELD_NAME = 'attachment_files';
    public const ATTACHMENT_COLLECTION_NAME = 'attachments';

    public const STATUS_NEW = 10;
    public const STATUS_PICKED_UP = 20;
    public const STATUS_DELIVERED = 30;

    public const INSPECTION_TYPE_HARD = 10;
    public const INSPECTION_TYPE_EASY = 20;
    public const INSPECTION_TYPE_NONE = 30;
    public const INSPECTION_TYPE_NONE_W_FILE = 40;

    public const INSPECTION_TYPES = [
        self::INSPECTION_TYPE_HARD => 'Hard',
        self::INSPECTION_TYPE_EASY => 'Easy',
        self::INSPECTION_TYPE_NONE => 'Without inspection',
        self::INSPECTION_TYPE_NONE_W_FILE => 'Without inspection (with file)',
    ];

    public const VIN_SCAN_FIELD_NAME = 'vin_scan';
    public const VIN_SCAN_COLLECTION_NAME = 'vin_scan';

    public const INSPECTION_DAMAGE_FIELD_NAME = 'damage_photo';
    public const INSPECTION_DAMAGE_COLLECTION_NAME = 'damage_photo';
    public const INSPECTION_DAMAGE_LABELED_COLLECTION_NAME = 'damage_labeled_photo';

    public const INSPECTION_PHOTO_FIELD_NAME = 'inspection_photo';
    public const INSPECTION_PHOTO_COLLECTION_NAME = 'inspection_photo';

    public const DOWNLOAD_PICKUP_INSPECTION_ARCHIVE_NAME = 'pickup-inspection.zip';
    public const DOWNLOAD_DELIVERY_INSPECTION_ARCHIVE_NAME = 'delivery-inspection.zip';

    public const CALCULATED_STATUS_DELETED = 'deleted';
    public const CALCULATED_STATUS_OFFER = 'offers';
    public const CALCULATED_STATUS_NEW = 'new';
    public const CALCULATED_STATUS_ASSIGNED = 'assigned';
    public const CALCULATED_STATUS_PICKED_UP = 'pickedup';
    public const CALCULATED_STATUS_DELIVERED = 'delivered';
    public const CALCULATED_STATUS_BILLED = 'billed';
    public const CALCULATED_STATUS_PAID = 'paid';

    public const STATUSES_LABEL = [
        self::CALCULATED_STATUS_NEW => 'New',
        self::CALCULATED_STATUS_PICKED_UP => 'Picked Up',
        self::CALCULATED_STATUS_DELIVERED => 'Delivered',
        self::CALCULATED_STATUS_ASSIGNED => 'Assigned'
    ];

    public const CALCULATED_STATUS_COLUMN_NAME = 'calculated_status';

    public const CALCULATED_SORTING_DATE_COLUMN_NAME = 'sorting_date';

    public const TABLE_NAME = 'orders';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'load_id',
        'inspection_type',
        'driver_id',
        'driver_pickup_id',
        'driver_delivery_id',
        'dispatcher_id',
        'instructions',
        'pickup_date',
        'delivery_date',
        'need_review',
        'dispatch_instructions',
        'pickup_comment',
        'delivery_comment',
        'shipper_comment',
        'deduct_from_driver',
        'deducted_note',
        'is_manual_change_to_pickup',
        'is_manual_change_to_delivery',
        'pickup_date_actual_tz',
        'delivery_date_actual_tz',
    ];

    protected $casts = [
        'pickup_contact' => 'array',
        'delivery_contact' => 'array',
        'shipper_contact' => 'array',
        'distance_data' => 'array',
        'pickup_time' => 'array',
        'delivery_time' => 'array',
        'is_billed' => 'boolean',
        'deduct_from_driver' => 'boolean',
        'is_manual_change_to_pickup' => 'boolean',
        'is_manual_change_to_delivery' => 'boolean',
        'pickup_date_data' => 'array',
        'delivery_date_data' => 'array',
        'pickup_date_actual_tz' => 'datetime',
        'delivery_date_actual_tz' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(
            function (self $model) {
                if ($model->isDirty()) {
                    $model->setContactNameFields();
                    $model->setCalculatedStatusField();
                    $model->setCompanyId();
                }
            }
        );

        self::deleted(
            function ($model) {
                $model->setCalculatedStatusDeleted();
            }
        );
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($this->getTable() . '.' . ($field ?? $this->getRouteKeyName()), $value)->first();
    }

    public function getInspectionType(): int
    {
        return $this->inspection_type ?? self::INSPECTION_TYPE_HARD;
    }

    public function setContactNameFields(): void
    {
        if ($this->pickup_contact && is_array($this->pickup_contact)) {
            $this->pickup_full_name = $this->pickup_contact['full_name'] ?? null;
        }

        if ($this->delivery_contact && is_array($this->delivery_contact)) {
            $this->delivery_full_name = $this->delivery_contact['full_name'] ?? null;
        }

        if ($this->shipper_contact && is_array($this->shipper_contact)) {
            $this->shipper_full_name = $this->shipper_contact['full_name'] ?? null;
        }
    }

    public function setCalculatedStatusField(): void
    {
        $this->calculated_status = $this->getCalculatedStatus();
    }

    public function getCalculatedStatus(): ?string
    {
        if ($this->trashed()) {
            return self::CALCULATED_STATUS_DELETED;
        }

        if ($this->isOffer()) {
            return self::CALCULATED_STATUS_OFFER;
        }

        if ($this->isStatusNew()) {
            return self::CALCULATED_STATUS_NEW;
        }

        if ($this->isStatusAssigned()) {
            return self::CALCULATED_STATUS_ASSIGNED;
        }

        if ($this->isStatusPickedUp()) {
            return self::CALCULATED_STATUS_PICKED_UP;
        }

        if ($this->isStatusDelivered()) {
            return self::CALCULATED_STATUS_DELIVERED;
        }

        return null;
    }

    public function isOffer(): bool
    {
        return (
            $this->isNew()
            && $this->dispatcher_id === null
        );
    }

    /**
     * Может соответствовать нескольким статусам(new|offer|assigned)
     */
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isStatusNew(): bool
    {
        return (
            $this->isNew()
            && $this->driver_id === null
            && $this->dispatcher_id !== null
        );
    }

    public function isStatusAssigned(): bool
    {
        return (
            $this->isNew()
            && $this->driver_id !== null
            && $this->dispatcher_id !== null
        );
    }

    public function isStatusPickedUp(): bool
    {
        return (
            $this->isPickedUp()
            && $this->driver_id !== null
            && $this->dispatcher_id !== null
        );
    }

    public function isPickedUp(): bool
    {
        return $this->status === self::STATUS_PICKED_UP;
    }

    public function isStatusDelivered(): bool
    {
        return $this->isDelivered();
    }

    /**
     * Фактическая проверка, что заказ доставлен,
     * может соответствовать другим статусам доставлен, Выставлен счет, оплачен
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function setCalculatedStatusDeleted(): void
    {
        DB::table(self::TABLE_NAME)
            ->where('id', $this->id)
            ->update(
                [
                    'calculated_status' => static::CALCULATED_STATUS_DELETED,
                ]
            );
    }

    public function user(): BelongsTo
    {
        $belongsTo = $this->belongsTo(User::class);
        /** @var SoftDeletes|BelongsTo $belongsTo */
        return $belongsTo->withTrashed();
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(Bonus::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(OrderComment::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'carrier_id');
    }

    public function paymentStages(): HasMany
    {
        return $this->hasMany(PaymentStage::class);
    }

    public function driver(): BelongsTo
    {
        $belongsTo = $this->belongsTo(User::class, 'driver_id');
        /** @var SoftDeletes|BelongsTo $belongsTo */
        return $belongsTo->withTrashed();
    }

    public function driverPickup(): BelongsTo
    {
        $belongsTo = $this->belongsTo(User::class, 'driver_pickup_id');
        /** @var SoftDeletes|BelongsTo $belongsTo */
        return $belongsTo->withTrashed();
    }

    public function driverDelivery(): BelongsTo
    {
        $belongsTo = $this->belongsTo(User::class, 'driver_delivery_id');
        /** @var SoftDeletes|BelongsTo $belongsTo */
        return $belongsTo->withTrashed();
    }

    public function dispatcher(): BelongsTo
    {
        $belongsTo = $this->belongsTo(User::class, 'dispatcher_id');
        /** @var SoftDeletes|BelongsTo $belongsTo */
        return $belongsTo->withTrashed();
    }

    public function payrolls(): BelongsToMany
    {
        return $this->belongsToMany(Payroll::class);
    }

    public function sendDocsDelayed(): HasMany
    {
        return $this->hasMany(SendDocsDelay::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(OrderSignature::class);
    }

    public function getImageClass(): string
    {
        return OrderImage::class;
    }

    public function getStateForCrm(): ?string
    {
        return $this->calculated_status;
    }

    public function getCategoryForMobileApp(): ?string
    {
        if (
            $this->isNew()
            && (
                (
                    $this->pickup_date > 0
                    && $this->pickup_date < strtotime('tomorrow')
                )
                || !$this->pickup_date
            )
        ) {
            return self::MOBILE_TAB_IN_WORK;
        }

        if (
            $this->has_pickup_inspection
            && $this->has_pickup_signature
            && $this->isPickedUp()
        ) {
            return self::MOBILE_TAB_IN_WORK;
        }

        if (
            $this->isNew()
            && $this->pickup_date >= strtotime('tomorrow')
        ) {
            return self::MOBILE_TAB_PLAN;
        }

        if (
            $this->has_delivery_inspection
            && $this->has_delivery_signature
            && $this->payment
            && !$this->payment->driver_payment_data_sent
            && $this->payment->customer_payment_amount
            && $this->isDelivered()
        ) {
            return self::MOBILE_TAB_IN_WORK;
        }

        if (
            $this->has_delivery_inspection
            && $this->has_delivery_signature
            && $this->isDelivered()
            && $this->delivery_date_actual >= now()->subDays(config('orders.mobile.history.days'))->getTimestamp()
        ) {
            return self::MOBILE_TAB_HISTORY;
        }

        return null;
    }

    public function getCustomerPickupSignature(): ?string
    {
        $image = $this->getFirstMedia(
            self::PICKUP_CUSTOMER_SIGNATURE_COLLECTION_NAME
        );

        if ($image) {
            return $image->getFullUrl();
        }

        return null;
    }

    public function getCustomerDeliverySignature(): ?string
    {
        $image = $this->getFirstMedia(
            self::DELIVERY_CUSTOMER_SIGNATURE_COLLECTION_NAME
        );

        if ($image) {
            return $image->getFullUrl();
        }

        return null;
    }

    public function getDriverPickupSignature(): ?string
    {
        $image = $this->getFirstMedia(
            self::PICKUP_DRIVER_SIGNATURE_COLLECTION_NAME
        );

        if ($image) {
            return $image->getFullUrl();
        }

        return null;
    }

    public function getDriverDeliverySignature(): ?string
    {
        $image = $this->getFirstMedia(
            self::DELIVERY_DRIVER_SIGNATURE_COLLECTION_NAME
        );

        if ($image) {
            return $image->getFullUrl();
        }

        return null;
    }

    public function hasDriver(): bool
    {
        return (bool)$this->driver_id;
    }

    public function isReleased(): bool
    {
        return $this->isOffer()
            && $this->wasChanged('dispatcher_id');
    }

    public function hasDispatcher(): bool
    {
        return (bool)$this->dispatcher_id;
    }

    public function isTaken(): bool
    {
        return $this->isStatusNew()
            && $this->wasChanged('dispatcher_id');
    }

    public function getPickupContact(): array
    {
        return $this->postProcessContact($this->pickup_contact ?? []);
    }

    public function postProcessContact(array $contact_data): array
    {
        if (isset($contact_data['state_id'])) {
            $state = State::find($contact_data['state_id']);
            if ($state) {
                $contact_data['state_short_name'] = $state->state_short_name;
            } else {
                $contact_data['state_short_name'] = '';
            }
        }

        if (isset($contact_data['city'])) {
            $cityArr = explode(',', $contact_data['city']);
            $contact_data['city'] = $cityArr[0];
        }

        return $contact_data;
    }

    public function getDeliveryContact(): array
    {
        return $this->postProcessContact($this->delivery_contact ?? []);
    }

    public function getPickupContactAsStr(): ?string
    {
        return $this->processContactAsStr(
            $this->postProcessContact($this->pickup_contact ?? [])
        );
    }

    public function getDeliveryContactAsStr(): ?string
    {
        return $this->processContactAsStr(
            $this->postProcessContact($this->delivery_contact ?? [])
        );
    }

    private function processContactAsStr(array $data): ?string
    {
        if(!isset($data['address'])) return null;

        $addr = $data['address'];

        if(isset($data['city'])) $addr .= ', ' . $data['city'];
        if(isset($data['state_short_name'])) $addr .= ', ' . $data['state_short_name'];

        return $addr;
    }

    public function getPaymentForMiles(): ?float
    {
        if(
            isset($this->distance_data['distance'])
            && isset($this->payment->total_carrier_amount)
        ){
            return round($this->payment->total_carrier_amount/$this->distance_data['distance'], 2);
        }

        return null;
    }

    public function getShipperContact(): array
    {
        return $this->postProcessContact($this->shipper_contact ?? []);
    }

    public function generatePublicToken(): string
    {
        return $this->public_token = hash('sha256', Str::random(60));
    }

    public function getPublicToken(): ?string
    {
        return $this->public_token;
    }

    public function getAttachments(): array
    {
        return $this
            ->getMedia(self::ATTACHMENT_COLLECTION_NAME)
            ->all();
    }

    public function setBilled(string $invoiceId, int $timestamp): void
    {
        $this->payment->invoice_id = $invoiceId;
        $this->payment->invoice_issue_date = $timestamp;
        $this->payment->save();

        $this->is_billed = true;
        $this->save();
    }

    public function setPickupCompletedFields($pickup_date_actual = null): void
    {
        $companyTimezone = $this->company->getTimezoneOrDefault();

        $time = $pickup_date_actual
            ? Carbon::parse($pickup_date_actual)
            : Carbon::now();

        $this->pickup_date_actual = $time
            ->getTimestamp();

        $this->pickup_date_actual_tz = Carbon::createFromTimestamp($this->pickup_date_actual)
            ->setTimezone($companyTimezone);

        $this->has_pickup_inspection = true;
        $this->has_pickup_signature = true;

        if (!$this->pickup_customer_full_name) {
            $this->pickup_customer_not_available = true;
        }

        $this->status = self::STATUS_PICKED_UP;
    }

    public function setDeliveryCompletedFields($delivery_date_actual = null): void
    {
        $companyTimezone = $this->company->getTimezoneOrDefault();

        $time = $delivery_date_actual ? Carbon::parse($delivery_date_actual) : Carbon::now();

        $this->delivery_date_actual = $time
            ->getTimestamp();

        $this->delivery_date_actual_tz = Carbon::createFromTimestamp($this->delivery_date_actual)
            ->setTimezone($companyTimezone);

        $this->has_delivery_inspection = true;
        $this->has_delivery_signature = true;

        if (!$this->delivery_customer_full_name) {
            $this->delivery_customer_not_available = true;
        }

        $this->status = self::STATUS_DELIVERED;
    }

    public function getAllowedStatusChangeList(): ?array
    {
        if ($this->isStatusAssigned()) {
            return [
                [
                    'id' => self::CALCULATED_STATUS_NEW,
                    'title' => 'New',
                ],
                [
                    'id' => self::CALCULATED_STATUS_PICKED_UP,
                    'title' => 'Picked up',
                ],
                [
                    'id' => self::CALCULATED_STATUS_DELIVERED,
                    'title' => 'Delivered',
                ],
            ];
        }

        if ($this->isStatusPickedUp()) {
            return [
                [
                    'id' => self::CALCULATED_STATUS_ASSIGNED,
                    'title' => 'Assigned',
                ],
                [
                    'id' => self::CALCULATED_STATUS_DELIVERED,
                    'title' => 'Delivered',
                ],
            ];
        }

        if ($this->isStatusDelivered()) {
            return [
                [
                    'id' => self::CALCULATED_STATUS_PICKED_UP,
                    'title' => 'Picked up',
                ],
            ];
        }

        return null;
    }

    public function ifNullDriverOrDispatcherAllowed(): bool
    {
        if ($this->isStatusPickedUp()) {
            return false;
        }

        return true;
    }

    protected function getPushService(): PushService
    {
        return resolve(PushService::class);
    }

    public function canGrantPickedUpStatus(): bool
    {
        return
            !$this->isPickedUp()
            && !$this->isDelivered()
            && $this->has_pickup_inspection
            && $this->has_pickup_signature
            && $this->driver_id !== null
            && $this->dispatcher_id !== null;
    }

    public function canGrantDeliveredStatus(): bool
    {
        return
            !$this->isDelivered()
            && $this->has_pickup_inspection
            && $this->has_pickup_signature
            && $this->has_delivery_inspection
            && $this->has_delivery_signature
            && $this->driver_id !== null
            && $this->dispatcher_id !== null;
    }

    /**
     * @return Diffable[]
     */
    public function getRelationsForDiff(): array
    {
        return [
            'payment' => $this->payment,
            'paymentStages' => $this->paymentStages,
            'vehicles' => $this->vehicles,
            'expenses' => $this->expenses,
            'bonuses' => $this->bonuses,
            'comments' => $this->comments,
            self::ATTACHMENT_COLLECTION_NAME => (
                new MediaCollection($this->getMedia(self::ATTACHMENT_COLLECTION_NAME))
            )->getAttributesForDiff(),
            'pickup_contact' => new ContactEntity($this->pickup_contact),
            'delivery_contact' => new ContactEntity($this->delivery_contact),
            'shipper_contact' => new ContactEntity($this->shipper_contact),
            'pickup_time' => new TimeEntity($this->pickup_time ?? []),
            'delivery_time' => new TimeEntity($this->delivery_time  ?? []),
            'tags' => array_column($this->tags->toArray(), 'name'),
        ];
    }

    public function getOverdue(): OverdueData
    {
        $data = OverdueData::make();
        if ($this->pickup_date && !$this->pickup_date_actual) {
            $time = Carbon::createFromFormat(
                "g:i A s",
                !empty($this->pickup_time['to']) ? $this->pickup_time['to'] . ' 59' : '11:59 PM 59'
            );
            $data->addPickup(
                Carbon::createFromTimestamp($this->pickup_date, $this->pickup_contact['timezone'] ?? null)
                    ->startOfDay()
                    ->addHours($time->hour)
                    ->addMinutes($time->minute)
                    ->addSeconds($time->second)
            );
        }
        if ($this->delivery_date && !$this->delivery_date_actual) {
            $time = Carbon::createFromFormat(
                "g:i A s",
                !empty($this->delivery_time['to']) ? $this->delivery_time['to'] . ' 59' : '11:59 PM 59'
            );
            $data->addDelivery(
                Carbon::createFromTimestamp($this->delivery_date, $this->delivery_contact['timezone'] ?? null)
                    ->startOfDay()
                    ->addHours($time->hour)
                    ->addMinutes($time->minute)
                    ->addSeconds($time->second)
            );
        }
        $payment = $this->payment;
        if (!$payment) {
            return $data;
        }
        $flags = $payment->paidFlags();
        if (!$flags->isBrokerPaid && $payment->broker_payment_planned_date) {
            $data->addBrokerPayment(Carbon::createFromTimestamp($payment->broker_payment_planned_date)->endOfDay());
        }
        if (!$flags->isCustomerPaid && $payment->customer_payment_planned_date) {
            $data->addCustomerPayment(Carbon::createFromTimestamp($payment->customer_payment_planned_date)->endOfDay());
        }
        if (!$flags->isBrokerFeePaid && $payment->broker_fee_planned_date) {
            $data->addBrokerFeePayment(Carbon::createFromTimestamp($payment->broker_fee_planned_date)->endOfDay());
        }
        return $data;
    }
}
