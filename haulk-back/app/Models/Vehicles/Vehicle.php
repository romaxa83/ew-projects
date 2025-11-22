<?php

namespace App\Models\Vehicles;

use App\Collections\Models\BodyShop\Orders\MediaCollection;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\DiffableInterface;
use App\Models\Files\BodyShop\VehicleImage;
use App\Models\Files\HasMedia;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Tags\HasTags;
use App\Models\Tags\HasTagsTrait;
use App\Models\Users\User;
use App\Models\Vehicles\Comments\Comment;
use App\Scopes\CompanyScope;
use App\Traits\Diffable;
use App\Traits\Models\Vehicles\DatesTrait;
use App\Traits\Models\WithBodyShopCompaniesTrait;
use App\Traits\SetCompanyId;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use FontLib\TrueType\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\Builders\Vehicles\TruckBuilder;

/**
 * @property int $id
 * @property string $unit_number
 * @property string $vin
 * @property string $make
 * @property string $model
 * @property string $year
 * @property string|null $license_plate
 * @property string|null $temporary_plate
 * @property int|null $owner_id
 * @property int|null $customer_id
 * @property int|null $driver_id
 * @property string|null $notes
 * @property int|null $broker_id
 * @property int|null $carrier_id
 * @property string|null $color
 * @property float|null $gvwr
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $inspection_number
 * @property Carbon|null $inspection_date
 * @property Carbon|null $inspection_expiration_date
 * @property string|null $registration_number
 * @property Carbon|null $registration_date,
 * @property Carbon|null $registration_expiration_date
 * @property int|null $gps_device_id
 * @property int|null $last_gps_history_id
 * @property Carbon|null $last_driving_at
 *
 * @see static::owner()
 * @property User|null owner
 *
 * @see static::customer()
 * @property VehicleOwner|null customer
 *
 * @see static::driver()
 * @property  User|null driver
 *
 * @see static::orders()
 * @property Order[] orders
 *
 * @see static::comments()
 * @property Comment[] comments
 *
 * @see static::scopeSort()
 * @method static Builder|static sort(string $column, string $direction)
 *
 * @see static::driversHistory()
 * @property VehicleDriverHistory[] driversHistory
 *
 * @see static::ownersHistory()
 * @property VehicleOwnerHistory[] ownersHistory
 *
 * @see static::gpsDevice()
 * @property  Device|null gpsDevice
 *
 * @see static::gpsDeviceWithTrashed()
 * @property  Device|null gpsDeviceWithTrashed
 *
 * @see static::lastGPSHistory()
 * @property  History|null lastGPSHistory
 *
 * @see static::gpsHistories()
 * @property History|Collection gpsHistories
 *
 * @mixin Eloquent
 */
abstract class Vehicle extends Model implements HasTags, HasMedia, DiffableInterface
{
    use Filterable;
    use SetCompanyId;
    use HasTagsTrait;
    use WithBodyShopCompaniesTrait;
    use SoftDeletes;
    use HasMediaTrait;
    use Diffable;
    use HasFactory;
    use DatesTrait;

    public const VEHICLE_TYPE_ATV = 1;
    public const VEHICLE_TYPE_BOAT = 2;
    public const VEHICLE_TYPE_COUPE_2 = 3;
    public const VEHICLE_TYPE_MOTORCYCLE = 7;
    public const VEHICLE_TYPE_PICKUP_4 = 8;
    public const VEHICLE_TYPE_PICKUP_2 = 9;
    public const VEHICLE_TYPE_SEDAN = 11;
    public const VEHICLE_TYPE_SUV = 12;
    public const VEHICLE_TYPE_TRAILER_BUMPER = 13;
    public const VEHICLE_TYPE_TRUCK_DAYCAB = 16;
    public const VEHICLE_TYPE_VAN = 18;
    public const VEHICLE_TYPE_OTHER = 19;

    public const VEHICLE_TYPES = [
        self::VEHICLE_TYPE_ATV => 'ATV',
        self::VEHICLE_TYPE_BOAT => 'Boat',
        self::VEHICLE_TYPE_COUPE_2 => 'Coupe (2 doors)',
        4 => 'Freight',
        5 => 'Heavy Machinery',
        6 => 'Livestock',
        self::VEHICLE_TYPE_MOTORCYCLE => 'Motorcycle',
        self::VEHICLE_TYPE_PICKUP_4 => 'Pickup (4 Doors)',
        self::VEHICLE_TYPE_PICKUP_2 => 'Pickup (2 Doors)',
        10 => 'RV',
        self::VEHICLE_TYPE_SEDAN => 'Sedan',
        self::VEHICLE_TYPE_SUV => 'SUV',
        self::VEHICLE_TYPE_TRAILER_BUMPER => 'Trailer (Bumper Pull)',
        14 => 'Trailer (Gooseneck)',
        15 => 'Trailer (5th Wheel)',
        self::VEHICLE_TYPE_TRUCK_DAYCAB => 'Truck (daycab)',
        17 => 'Truck (with sleeper)',
        self::VEHICLE_TYPE_VAN => 'Van',
        self::VEHICLE_TYPE_OTHER => 'Other',
    ];

    public const VEHICLE_FORM_TRUCK = 'truck';
    public const VEHICLE_FORM_TRAILER = 'trailer';

    public const ATTACHMENT_COLLECTION_NAME = 'attachments';

    public const ATTACHMENT_FIELD_NAME = 'attachment_files';

    public const REGISTRATION_DOCUMENT_NAME = 'registration_file';
    public const INSPECTION_DOCUMENT_NAME = 'inspection_file';

    protected $connection = 'pgsql';

    protected $fillable = [
        'unit_number',
        'vin',
        'make',
        'model',
        'year',
        'license_plate',
        'temporary_plate',
        'notes',
        'owner_id',
        'driver_id',
        'carrier_id',
        'broker_id',
        'customer_id',
        'color',
        'gvwr',
        'registration_number',
        'registration_date',
        'registration_expiration_date',
        'inspection_date',
        'inspection_expiration_date',
        'gps_device_id',
        'last_gps_history_id',
        'last_driving_at'
    ];

    protected $casts = [
        'registration_date' => 'date',
        'registration_expiration_date' => 'date',
        'inspection_date' => 'date',
        'inspection_expiration_date' => 'date',
        'last_driving_at' => 'datetime',
        'float' => 'gvwr',
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::saving(
            function (self $model) {
                $model->setCompanyId();
            }
        );
    }

    abstract public function comments(): HasMany;

    public function getCompany(): ?Company
    {
        if ($this->broker_id) {
            return Company::find($this->broker_id);
        }

        if ($this->carrier_id) {
            return Company::find($this->carrier_id);
        }

        return null;
    }

    public function getCompanyId(): int
    {
        if ($this->broker_id) {
            return Company::find($this->broker_id)->getCompanyId();
        }

        if ($this->carrier_id) {
            return Company::find($this->carrier_id)->getCompanyId();
        }

        return 0;
    }

    public function owner(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id')
            ->withoutGlobalScope(new CompanyScope());
    }

    public function customer(): ?BelongsTo
    {
        return $this->belongsTo(VehicleOwner::class, 'customer_id');
    }

    public function driver(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id')
            ->withoutGlobalScope(new CompanyScope());
    }

    public function scopeSort(Builder $builder, string $column, string $direction = 'asc')
    {
        if ($column === 'company_name') {
            return $builder->selectSub(
                Company::query()
                    ->select('name')
                    ->whereRaw('companies.id in (carrier_id, broker_id)')
                    ->getQuery(),
                'company_name'
            )->orderBy('company_name', $direction);
        }


        return $builder->orderBy($column, $direction);
    }

    public static function getTypesList(): array
    {
        $data = [];

        foreach (self::VEHICLE_TYPES as $k => $v) {
            $data[] = [
                'id' => $k,
                'title' => $v,
            ];
        }

        return $data;
    }

    public function getOwnerFullName(): ?string
    {
        if ($this->owner) {
            return $this->owner->full_name;
        }

        if ($this->customer) {
            return $this->customer->getFullName();
        }

        return null;
    }

    abstract public function orders(): HasMany;

    public function hasRelatedOpenOrders(): bool
    {
        return $this->orders()->open()->exists();
    }

    public function hasRelatedDeletedOrders(): bool
    {
        return $this->orders()->onlyTrashed()->exists();
    }

    public function hasRelatedClosedOrders(): bool
    {
        return $this->orders()->closed()->exists();
    }

    public function getImageClass(): string
    {
        return VehicleImage::class;
    }

    public function getAttachments(): array
    {
        return $this
            ->getMedia(self::ATTACHMENT_COLLECTION_NAME)
            ->all();
    }

    public function isBodyShopVehicle(): bool
    {
        return empty($this->carrier_id) && empty($this->broker_id);
    }

    public function getRelationsForDiff(): array
    {
        return [
            'comments' => $this->comments,
            self::ATTACHMENT_COLLECTION_NAME => (
                    new MediaCollection($this->getMedia(self::ATTACHMENT_COLLECTION_NAME))
                )->getAttributesForDiff(),
            self::REGISTRATION_DOCUMENT_NAME => (
                    new MediaCollection($this->getMedia(self::REGISTRATION_DOCUMENT_NAME))
                )->getAttributesForDiff(),
            self::INSPECTION_DOCUMENT_NAME => (
                    new MediaCollection($this->getMedia(self::INSPECTION_DOCUMENT_NAME))
                )->getAttributesForDiff(),
        ];
    }

    public function getFile(string $fileName)
    {
        return $this
            ->getMedia($fileName)
            ->first();
    }

    public function isRegistrationDocumentExpires(): bool
    {
        return $this->registration_expiration_date
                && now()->startOfDay()->addDays(config('trucks_trailers.registration_expiration_alert_in_days')) >= $this->registration_expiration_date;
    }

    public function isInspectionDocumentExpires(): bool
    {
        return $this->inspection_expiration_date
            && now()->startOfDay()->addDays(config('trucks_trailers.inspection_expiration_alert_in_days')) >= $this->inspection_expiration_date;
    }

    abstract public function driversHistory(): HasMany;

    abstract public function ownersHistory(): HasMany;

    public function gpsDevice(): HasOne
    {
        return $this->hasOne(Device::class, 'id', 'gps_device_id');
    }

    public function gpsDeviceWithTrashed(): HasOne
    {
        return $this->gpsDevice()->withTrashed();
    }

    public function lastGPSHistory(): HasOne
    {
        return $this->hasOne(History::class, 'id',  'last_gps_history_id');
    }

    public function isTruck(): bool
    {
        return $this instanceof Truck;
    }

    public function isTrailer(): bool
    {
        return $this instanceof Trailer;
    }

    public function gpsHistories(): HasMany
    {
        return $this->hasMany(History::class);
    }

    public function histories(): MorphMany
    {
        return $this->morphMany(\App\Models\History\History::class, 'model');
    }
}
