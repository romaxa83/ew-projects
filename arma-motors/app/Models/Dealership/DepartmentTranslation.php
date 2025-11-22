<?php

namespace App\Models\Dealership;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property bool $dealership_id
 * @property string $lang
 * @property string $name
 * @property string $address
 *
 */

class DepartmentTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'dealership_department_translations';

    protected $table = self::TABLE;
}
