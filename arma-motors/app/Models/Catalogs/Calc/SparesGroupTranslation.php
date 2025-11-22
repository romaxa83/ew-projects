<?php

namespace App\Models\Catalogs\Calc;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $group_id
 * @property string $lang
 * @property string $name
 * @property string $unit
 *
 */

class SparesGroupTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'spares_group_translations';
    protected $table = self::TABLE;
}
