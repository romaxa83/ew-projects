<?php

namespace App\Models\Vehicles;

use App\ModelFilters\Vehicles\TrailerFilter;
use App\Models\BodyShop\Orders\Order;
use App\Models\Vehicles\Comments\TrailerComment;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property float|null $gvwr
 */

class Trailer extends Vehicle
{
   public const TABLE_NAME = 'trailers';

   protected $table = self::TABLE_NAME;

    protected $fillable = [
        'unit_number',
        'vin',
        'make',
        'model',
        'year',
        'type',
        'license_plate',
        'temporary_plate',
        'notes',
        'owner_id',
        'driver_id',
        'carrier_id',
        'broker_id',
        'customer_id',
        'color',
        'registration_number',
        'registration_date',
        'registration_expiration_date',
        'inspection_number',
        'inspection_date',
        'inspection_expiration_date',
        'gps_device_id',
        'last_gps_history_id',
        'gvwr',
        'registration_date_as_str',
        'registration_expiration_date_as_str',
        'inspection_date_as_str',
        'inspection_expiration_date_as_str',
    ];

    protected $casts = [
        'registration_expiration_date' => 'date',
        'registration_date' => 'date',
        'inspection_date' => 'date',
        'inspection_expiration_date' => 'date',
        'gvwr' => 'float',
    ];

   public function modelFilter()
   {
        return $this->provideFilter(TrailerFilter::class);
   }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'trailer_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TrailerComment::class);
    }

    public function driversHistory(): HasMany
    {
        return $this->hasMany(TrailerDriverHistory::class, 'trailer_id');
    }

    public function ownersHistory(): HasMany
    {
        return $this->hasMany(TrailerOwnerHistory::class, 'trailer_id');
    }
}
