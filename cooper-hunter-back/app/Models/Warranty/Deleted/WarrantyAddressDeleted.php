<?php

namespace App\Models\Warranty\Deleted;

use App\Models\BaseModel;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Traits\HasFactory;
use Database\Factories\Warranty\Deleted\WarrantyAddressDeletedFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int warranty_id
 * @property int country_id
 * @property int state_id
 * @property string city
 * @property string street
 * @property string zip
 *
 * @see WarrantyAddress::state()
 * @property-read State state
 *
 * @see WarrantyAddress::country()
 * @property-read Country country
 *
 * @method static WarrantyAddressDeletedFactory factory(...$parameters)
 */
class WarrantyAddressDeleted extends BaseModel
{
    use HasFactory;

    public const TABLE = 'warranty_address_deleteds';

    public $timestamps = false;

    public function state(): BelongsTo|State
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo|Country
    {
        return $this->belongsTo(Country::class);
    }
}


