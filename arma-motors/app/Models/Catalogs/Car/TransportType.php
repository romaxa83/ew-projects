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
class TransportType extends BaseModel
{
    public $timestamps = false;

    public const TABLE = 'transport_types';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(TransportTypeTranslation::class, 'transport_type_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(TransportTypeTranslation::class,'transport_type_id', 'id')->where('lang', \App::getLocale());
    }
}
