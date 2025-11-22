<?php

namespace App\Models\Vehicles;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Contracts\HasComments;
use App\Foundations\Modules\Comment\Traits\InteractsWithComment;
use App\Foundations\Modules\History\Contracts\HasHistory;
use App\Foundations\Modules\History\Traits\InteractsWithHistory;
use App\Foundations\Modules\Media\Contracts\HasMedia;
use App\Foundations\Modules\Media\Images\VehicleImage;
use App\Foundations\Modules\Media\Traits\InteractsWithMedia;
use App\Models\Companies\Company;
use App\Models\Customers\Customer;
use App\Models\Orders\BS\Order;
use App\Models\Tags\HasTags;
use App\Models\Tags\HasTagsTrait;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int id
 * @property int|null origin_id
 * @property int|null customer_id
 * @property string vin
 * @property string unit_number
 * @property string make
 * @property string model
 * @property string year
 * @property string|null color
 * @property float|null $gvwr
 * @property int type
 * @property string license_plate
 * @property string temporary_plate
 * @property string|null notes
 * @property int|null company_id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon|null deleted_at
 *
 * @see self::customer()
 * @property BelongsTo|Customer customer
 *
 * @see self::orders()
 * @property Order[]|MorphMany orders
 *
 * @see self::company()
 * @property BelongsTo|Company company
 *
 * @mixin Eloquent
 */
class Vehicle extends BaseModel implements
    HasTags,
    HasMedia,
    HasComments,
    HasHistory
{
    use HasFactory;
    use SoftDeletes;
    use HasTagsTrait;
    use InteractsWithMedia;
    use InteractsWithComment;
    use InteractsWithHistory;

    public const ATTACHMENT_COLLECTION_NAME = 'attachments';
    public const ATTACHMENT_FIELD_NAME = 'attachment_files';
    public const MAX_ATTACHMENTS_COUNT = 5;

    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'gvwr' => 'float',
    ];

    public const ALLOWED_SORTING_FIELDS = [
        'company_name',
    ];

    public function isTruck(): bool
    {
        return $this instanceof Truck;
    }

    public function isTrailer(): bool
    {
        return $this instanceof Trailer;
    }

    public function getImageClass(): string
    {
        return VehicleImage::class;
    }

    public function getAttachments(): array
    {
        return $this->getMedia(self::ATTACHMENT_COLLECTION_NAME)
            ->all();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function dataForUpdateHistory(): array
    {
        $old = $this->getAttributes();
        $old['tags'] = $this->tags()->get()->getNamesAsString();
        $old['media'] = $this->media()->get();

        return $old;
    }

    public function getMorphName(): string
    {
        return $this::MORPH_NAME;
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'vehicle');
    }

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
}
