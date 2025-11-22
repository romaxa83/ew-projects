<?php

namespace App\Models\Dealership;

use App\Models\BaseModel;

/**
 *
 * @property int $id
 * @property int $day
 * @property int|null $from     // миллисекунды от начала дня
 * @property int|null $to       // миллисекунды от начала дня
 * @property int $department_id
 */
class Schedule extends BaseModel
{
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

    public $timestamps = false;

    public const TABLE = 'dealership_department_schedules';

    protected $table = self::TABLE;

    public function getTimeAttribute(): null|array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}

