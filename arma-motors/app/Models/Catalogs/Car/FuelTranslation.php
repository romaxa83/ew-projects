<?php

namespace App\Models\Catalogs\Car;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $model_id
 * @property string $lang
 * @property string $name
 *
 */

class FuelTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'fuel_translations';

    protected $table = self::TABLE;
}

