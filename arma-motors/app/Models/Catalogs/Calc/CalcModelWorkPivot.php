<?php

namespace App\Models\Catalogs\Calc;

use App\Casts\VolumeCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $model_id
 * @property int $work_id
 * @property int $minutes
 *
 */
class CalcModelWorkPivot extends BaseModel
{
    use HasFactory;

    public const TABLE = 'calc_model_work_pivot';
    protected $table = self::TABLE;

    protected $casts = [
        'minutes' => VolumeCast::class
    ];
}
