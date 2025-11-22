<?php

namespace App\Models\Warranty\WarrantyInfo;

use App\Models\BaseModel;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Traits\HasFactory;
use Database\Factories\Warranty\WarrantyInfo\WarrantyAddressFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Volosyuk\SimpleEloquent\SimpleEloquent;

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
 * @method static WarrantyAddressFactory factory(...$parameters)
 */
class WarrantyAddress extends BaseModel
{
    use HasFactory;
    use SimpleEloquent;

    public const TABLE = 'warranty_addresses';

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

