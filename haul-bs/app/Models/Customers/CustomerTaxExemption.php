<?php

namespace App\Models\Customers;

use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Enums\Customers\CustomerTaxExemptionType;
use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Media\Contracts\HasMedia;
use Carbon\Carbon;
use Database\Factories\Customers\CustomerTaxExemptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


/**
 * @property int id
 * @property int customer_id
 * @property CustomerTaxExemptionStatus status
 * @property string|null link
 * @property string|null file_name
 * @property CustomerTaxExemptionType|null type
 *
 * @property Carbon|null date_active_to
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property-read Customer customer
 * @property-read Media file
 *
 * @method static static|Builder query()
 *
 * @method Collection|static[] get()
 *
 * @see self::scopeWhereEmail()
 * @method Builder|static whereEmail(string $email)
 *
 * @method static CustomerTaxExemptionFactory factory(...$parameters)
 */
class CustomerTaxExemption extends BaseModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use Notifiable;

    public const TABLE = 'customer_tax_exemption';

    protected $table = self::TABLE;

    protected $fillable = [
        'customer_id',
        'status',
        'link',
        'file_name',
        'type',
        'date_active_to',
    ];

    protected $casts = [
        'date_active_to' => 'date',
        'status' => CustomerTaxExemptionStatus::class,
        'type' => CustomerTaxExemptionType::class,
    ];

    protected static function newFactory(): Factory
    {
        return CustomerTaxExemptionFactory::new();
    }

    public function getFileAttribute(): ?Media
    {
        return $this->getFirstMedia();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function getMediaCollectionName(): string
    {
        return 'default';
    }
}
