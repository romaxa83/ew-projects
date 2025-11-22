<?php

namespace App\Models\Catalogs\Calc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $model_id
 * @property string $lang
 * @property string $name
 *
 */

class WorkTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'work_translations';
    protected $table = self::TABLE;
}
