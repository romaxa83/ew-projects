<?php

namespace App\Models\Customers;

use App\Enums\Customers\AddressType;
use App\Foundations\Casts\Contact\PhoneCast;
use App\Foundations\Models\BaseModel;
use App\Foundations\Traits\Models\FullNameTrait;
use App\Foundations\ValueObjects\Phone;
use Database\Factories\Customers\AddressFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int customer_id
 * @property AddressType type
 * @property bool is_default
 * @property bool from_ecomm
 * @property string first_name
 * @property string last_name
 * @property string|null company_name
 * @property string address
 * @property string city
 * @property string state
 * @property string zip
 * @property Phone phone
 * @property string sort
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see self::customer()
 * @property Customer|BelongsTo customer
 *
 * @mixin Eloquent
 *
 * @method static AddressFactory factory(...$parameters)
 */
class Address extends BaseModel
{
    use HasFactory;
    use FullNameTrait;

    public const TABLE = 'customer_addresses';
    protected $table = self::TABLE;

    public const DEFAULT_TYPE = AddressType::Delivery->value;
    public const MAX_COUNT = 5;
    public const DEFAULT_SORT = 100;

    /** @var array<int, string> */
    protected $fillable = [
        'is_default',
        'sort'
    ];

    /** @var array<string, string> */
    protected $casts = [
        'type' => AddressType::class,
        'phone' => PhoneCast::class,
    ];

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function fromEcomm(): bool
    {
        return $this->from_ecomm;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
