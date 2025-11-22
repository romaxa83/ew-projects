<?php

namespace App\Models\Catalogs\Car;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $transmission_id
 * @property string $lang
 * @property string $name
 *
 */

class TransmissionTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'transmission_translations';

    protected $table = self::TABLE;
}

