<?php

namespace App\Models\Catalogs\Service;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 *
 */
class DriverAge extends BaseModel
{

    public $timestamps = false;

    public const TABLE = 'driver_ages';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(DriverAgeTranslation::class, 'driver_age_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(DriverAgeTranslation::class,'driver_age_id', 'id')->where('lang', \App::getLocale());
    }
}
