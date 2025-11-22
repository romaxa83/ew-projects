<?php

namespace App\Models\BodyShop\VehicleOwners;

use App\ModelFilters\BodyShop\VehicleOwners\VehicleOwnerFilter;
use App\Models\Files\BodyShop\VehicleOwnerImage;
use App\Models\Files\HasMedia;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\Saas\Company\Company;
use App\Models\Tags\HasTags;
use App\Models\Tags\HasTagsTrait;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Traits\SetCompanyId;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\VehicleOwners\VehicleOwner
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $phone
 * @property string $phone_extension
 * @property array|null $phones
 * @property string $email
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see static::trucks()
 * @property Truck[]|null trucks
 *
 * @see static::trailers()
 * @property Trailer[]|null trailers
 *
 * @see static::comments()
 * @property VehicleOwnerComment[] comments
 *
 * @mixin Eloquent
 */
class VehicleOwner extends Model implements HasMedia, HasTags
{
    use Filterable;
    use HasMediaTrait;
    use SetCompanyId;
    use HasTagsTrait;

    public const TABLE_NAME = 'bs_vehicle_owners';

    public const ATTACHMENT_FIELD_NAME = 'attachment_files';

    public const ATTACHMENT_COLLECTION_NAME = 'attachments';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'phone',
        'phone_extension',
        'phones',
        'email',
        'notes',
    ];

    protected $casts = [
        'phones' => 'array',
    ];

    /**
     * @return string
     */
    public function modelFilter()
    {
        return $this->provideFilter(VehicleOwnerFilter::class);
    }

    public function getImageClass(): string
    {
        return VehicleOwnerImage::class;
    }

    public function getAttachments(): array
    {
        return $this
            ->getMedia(self::ATTACHMENT_COLLECTION_NAME)
            ->all();
    }

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

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function trucks(): HasMany
    {
        return $this->hasMany(Truck::class, 'customer_id');
    }

    public function trailers(): HasMany
    {
        return $this->hasMany(Trailer::class, 'customer_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(VehicleOwnerComment::class, 'vehicle_owner_id');
    }
}
