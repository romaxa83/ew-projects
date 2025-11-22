<?php

namespace App\Models\Catalogs\Car;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $transport_type_id
 * @property string $lang
 * @property string $name
 *
 */

class TransportTypeTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'transport_type_translations';

    protected $table = self::TABLE;
}
