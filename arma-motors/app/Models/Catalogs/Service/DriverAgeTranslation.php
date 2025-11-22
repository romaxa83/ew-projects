<?php

namespace App\Models\Catalogs\Service;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $driver_age_id
 * @property string $lang
 * @property string $name
 *
 */

class DriverAgeTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'driver_age_translations';

    protected $table = self::TABLE;
}

