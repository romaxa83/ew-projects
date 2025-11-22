<?php

namespace App\Models\Users;

use App\Collections\Models\Orders\MediaCollection;
use App\Models\DiffableInterface;
use App\Models\Files\HasMedia;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\Files\UserDocumentImage;
use App\Models\Locations\State;
use App\Traits\Diffable;
use App\Traits\Models\Users\DriverLicenseDateTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Users\DriverLicense
 *
 * @property int $id
 * @property int $driver_id
 * @property string|null $license_number
 * @property Carbon|null $issuing_date
 * @property Carbon|null $expiration_date
 * @property string|null $issuing_date_as_str
 * @property string|null $expiration_date_as_str
 * @property string|null $issuing_country
 * @property int|null $issuing_state_id
 * @property string|null $category
 * @property string|null $category_name
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property string $type
 *
 * @property-read User $driver
 * @property-read State $issuingState
 *
 * @see DriverLicense::scopeCurrent()
 * @method static Builder|DriverLicense current()
 *
 * @see DriverLicense::scopePrevious()
 * @method static Builder|DriverLicense previous()
 *
 * @mixin Eloquent
 */
class DriverLicense extends Model implements HasMedia, DiffableInterface
{
    use HasFactory;
    use HasMediaTrait;
    use Diffable;
    use DriverLicenseDateTrait;

    const TABLE_NAME = 'driver_licenses';

    public const CATEGORY_A = 'A';
    public const CATEGORY_B = 'B';
    public const CATEGORY_C = 'C';
    public const CATEGORY_D = 'D';
    public const CATEGORY_OTHER = 'other';

    public const CATEGORIES = [
        self::CATEGORY_A => 'A',
        self::CATEGORY_B => 'B',
        self::CATEGORY_C => 'C',
        self::CATEGORY_D => 'D',
        self::CATEGORY_OTHER => 'Other',
    ];

    public const TYPE_CURRENT = 'current';
    public const TYPE_PREVIOUS = 'previous';

    public const ATTACHED_DOCUMENT_FILED_NAME = 'attached_document';

    public $table = self::TABLE_NAME;

    public $fillable = [
        'driver_id',
        'license_number',
        'issuing_date',
        'expiration_date',
        'issuing_date_as_str',
        'expiration_date_as_str',
        'issuing_country',
        'issuing_state_id',
        'category',
        'category_name',
        'type',
    ];

    protected $casts = [
        'issuing_date' => 'date',
        'expiration_date' => 'date'
    ];


    /**
     * @return BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id');
    }

    public function state(): HasOne
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function getImageClass(): string
    {
        return UserDocumentImage::class;
    }

    public function scopeCurrent(Builder $builder): Builder
    {
        return $builder->where('type', self::TYPE_CURRENT);
    }

    public function scopePrevious(Builder $builder): Builder
    {
        return $builder->where('type', self::TYPE_PREVIOUS);
    }

    public function getDocument()
    {
        return $this->getMedia(self::ATTACHED_DOCUMENT_FILED_NAME)
            ->first();
    }

    public function issuingState(): HasOne
    {
        return $this->hasOne(State::class, 'id', 'issuing_state_id');
    }

    public function getRelationsForDiff(): array
    {
        return [
            self::ATTACHED_DOCUMENT_FILED_NAME =>
                (new MediaCollection($this->getMedia(self::ATTACHED_DOCUMENT_FILED_NAME)))
                    ->getAttributesForDiff(),
        ];
    }
}
