<?php

namespace App\Models\Catalogs\Car;

use App\Casts\VolumeCast;
use App\Models\BaseModel;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 * @property int $volume
 *
 */
class EngineVolume extends BaseModel
{
    public $timestamps = false;

    public const TABLE = 'car_engine_volumes';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool',
        'volume' => VolumeCast::class
    ];
}
