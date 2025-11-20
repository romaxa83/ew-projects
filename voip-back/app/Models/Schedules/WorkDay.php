<?php

namespace App\Models\Schedules;

use App\Enums\Formats\DayEnum;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveTrait;
use Database\Factories\Schedules\WorkDayFactory;

/**
 * @property int id
 * @property int schedule_id
 * @property DayEnum name
 * @property string|null start_work_time
 * @property string|null end_work_time
 * @property bool active
 * @property int sort
 *
 * @method static WorkDayFactory factory(int $number = null)
 */
class WorkDay extends BaseModel
{
    use HasFactory;
    use ActiveTrait;

    public $timestamps = false;

    protected $table = self::TABLE;
    public const TABLE = 'schedule_days';

    protected $fillable = [];

    protected $dates = [];

    protected $casts = [
        'name' => DayEnum::class,
        'active' => 'boolean',
    ];
}
