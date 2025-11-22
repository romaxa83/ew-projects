<?php

namespace App\Models\Catalogs\Car;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 *
 */
class Transmission extends BaseModel
{
    public $timestamps = false;

    public const TABLE = 'transmissions';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(TransmissionTranslation::class, 'transmission_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(TransmissionTranslation::class,'transmission_id', 'id')->where('lang', \App::getLocale());
    }
}
