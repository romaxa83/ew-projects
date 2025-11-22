<?php

namespace App\Models\Catalogs\Service;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $privileges_id
 * @property string $lang
 * @property string $name
 *
 */

class PrivilegesTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'privileges_translations';

    protected $table = self::TABLE;
}
