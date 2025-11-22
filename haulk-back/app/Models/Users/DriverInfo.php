<?php

namespace App\Models\Users;

use App\Collections\Models\Orders\MediaCollection;
use App\Models\DiffableInterface;
use App\Models\Files\HasMedia;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\Files\UserDocumentImage;
use App\Traits\Diffable;
use App\Traits\Models\Users\DriverMedicalDateTrait;
use Carbon\Carbon;
use Database\Factories\Users\DriverInfoFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Users\DriverInfo
 *
 * @property int $id
 * @property int $driver_id
 * @property int|null $driver_rate
 * @property string|null $notes
 * @property string|null $medical_card_number
 * @property Carbon|null $medical_card_issuing_date
 * @property Carbon|null $medical_card_expiration_date
 * @property Carbon|null $mvr_reported_date
 * @property string|null $medical_card_issuing_date_as_str
 * @property string|null $medical_card_expiration_date_as_str
 * @property string|null $mvr_reported_date_as_str
 * @property bool $has_company
 * @property string|null $company_name
 * @property string|null $company_ein
 * @property string|null $company_address
 * @property string|null $company_city
 * @property string|null $company_zip
 *
 * @property-read User $driver
 * @method static Builder|DriverInfo newModelQuery()
 * @method static Builder|DriverInfo newQuery()
 * @method static Builder|DriverInfo query()
 * @method static Builder|DriverInfo whereDriverId($value)
 * @method static Builder|DriverInfo whereDriverLicenseNumber($value)
 * @method static Builder|DriverInfo whereId($value)
 * @method static Builder|DriverInfo whereTrailerCapacity($value)
 * @mixin Eloquent
 *
 * @method static DriverInfoFactory factory(...$parameters)
 */
class DriverInfo extends Model implements HasMedia, DiffableInterface
{
    use HasFactory;
    use HasMediaTrait;
    use Diffable;
    use DriverMedicalDateTrait;

    const TABLE_NAME = 'driver_information';

    public const ATTACHED_MEDICAL_CARD_FILED_NAME = 'medical_card_document';
    public const ATTACHED_MVR_FILED_NAME = 'mvr_document';

    public $table = 'driver_information';

    public $timestamps = false;

    public $fillable = [
        'driver_id',
        'driver_rate',
        'notes',
        'medical_card_number',
        'medical_card_issuing_date',
        'medical_card_expiration_date',
        'mvr_reported_date',
        'medical_card_issuing_date_as_str',
        'medical_card_expiration_date_as_str',
        'mvr_reported_date_as_str',
        'has_company',
        'company_name',
        'company_ein',
        'company_address',
        'company_city',
        'company_zip',
    ];

    protected $casts = [
        'medical_card_issuing_date' => 'date',
        'medical_card_expiration_date' => 'date',
        'mvr_reported_date' => 'date',
        'has_company' => 'boolean',
    ];
    /**
     * @return BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id');
    }

    public function getImageClass(): string
    {
        return UserDocumentImage::class;
    }

    public function getMedicalCardDocument()
    {
        return $this->getMedia(self::ATTACHED_MEDICAL_CARD_FILED_NAME)
            ->first();
    }

    public function getMvrDocument()
    {
        return $this->getMedia(self::ATTACHED_MVR_FILED_NAME)
            ->first();
    }

    public function getRelationsForDiff(): array
    {
        return [
            self::ATTACHED_MEDICAL_CARD_FILED_NAME =>
                (new MediaCollection($this->getMedia(self::ATTACHED_MEDICAL_CARD_FILED_NAME)))
                    ->getAttributesForDiff(),
            self::ATTACHED_MVR_FILED_NAME =>
                (new MediaCollection($this->getMedia(self::ATTACHED_MVR_FILED_NAME)))
                    ->getAttributesForDiff(),
        ];
    }
}
