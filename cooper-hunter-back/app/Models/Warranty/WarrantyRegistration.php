<?php

namespace App\Models\Warranty;

use App\Casts\Warranty\WarrantyProductInfoCast;
use App\Casts\Warranty\WarrantyUserInfoCast;
use App\Entities\Warranty\WarrantyProductInfo;
use App\Entities\Warranty\WarrantyUserInfo;
use App\Enums\Projects\Systems\WarrantyStatus;
use App\Enums\Warranties\WarrantyType;
use App\Filters\Warranty\WarrantyRegistrationFilter;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Models\Commercial\CommercialProject;
use App\Models\Projects\System;
use App\Models\Warranty\WarrantyInfo\WarrantyAddress;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\DataHash;
use BenSampo\Enum\Traits\CastsEnums;
use Database\Factories\Warranty\WarrantyRegistrationFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Volosyuk\SimpleEloquent\SimpleEloquent;

/**
 * @property int id
 * @property WarrantyStatus warranty_status
 * @property WarrantyType type
 * @property string|null notice
 * @property string|null member_type
 * @property int|null member_id
 * @property int|null system_id
 * @property int|null commercial_project_id
 * @property WarrantyUserInfo user_info
 * @property WarrantyProductInfo product_info
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property string|null hash
 *
 * @see WarrantyRegistration::address()
 * @property-read WarrantyAddress address
 *
 * @see WarrantyRegistration::commercialProject()
 * @property-read CommercialProject commercialProject
 *
 * @see WarrantyRegistration::system()
 * @property-read System system
 *
 * @method static WarrantyRegistrationFactory factory(...$parameters)
 */
class WarrantyRegistration extends BaseModel
{
    use HasFactory;
    use CastsEnums;
    use Filterable;
    use DataHash;
    use SimpleEloquent;

    public const TABLE = 'warranty_registrations';

    protected $table = self::TABLE;

    protected $fillable = [
        'notice',
        'warranty_status',
        'user_info',
        'product_info',
        'type',
        'commercial_project_id',
    ];

    protected $casts = [
        'user_info' => WarrantyUserInfoCast::class,
        'product_info' => WarrantyProductInfoCast::class,
        'warranty_status' => WarrantyStatus::class,
        'type' => WarrantyType::class,
    ];

    public function modelFilter(): string
    {
        return WarrantyRegistrationFilter::class;
    }

    public function member(): MorphTo
    {
        return $this->morphTo();
    }

    public function system(): BelongsTo|System
    {
        return $this->belongsTo(System::class);
    }

    public function commercialProject(): BelongsTo|CommercialProject
    {
        return $this->belongsTo(CommercialProject::class);
    }

    public function address(): BelongsTo|WarrantyAddress
    {
        return $this->belongsTo(WarrantyAddress::class, 'id', 'warranty_id');
    }

    public function unitsPivot(): HasMany|WarrantyRegistrationUnitPivot
    {
        return $this->hasMany(WarrantyRegistrationUnitPivot::class);
    }

    public function units(): BelongsToMany|Product
    {
        return $this->belongsToMany(Product::class, WarrantyRegistrationUnitPivot::TABLE)
            ->using(WarrantyRegistrationUnitPivot::class)
            ->as('unit')
            ->withPivot('serial_number');
    }

    /**
     * Не придумал как 'sync' 1 товар и много пивот значений, по-этому отзеркалил метод @see units()
     */
    public function unitsBySerial(): BelongsToMany|Product
    {
        return $this->belongsToMany(
            Product::class,
            WarrantyRegistrationUnitPivot::TABLE,
            'warranty_registration_id',
            'serial_number',
            'id',
        )
            ->using(WarrantyRegistrationUnitPivot::class);
    }

    public function getDataForHash(array $serialNumber = []): array
    {
        $tmp = $this
            ->toArray()
        ;

        unset(
            $tmp->created_at,
            $tmp->updated_at,
            $tmp->hash,
            $tmp->id,
        );

        $data = $tmp;
        $data['serial_numbers'] = $serialNumber;

        return $data;
    }
}
