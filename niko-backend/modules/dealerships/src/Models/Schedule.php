<?php

namespace WezomCms\Dealerships\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int $id
 * @property string $day
 * @property string|null $work_start
 * @property string|null $work_end
 * @property string|null $break_start
 * @property string|null $break_end
 * @property int $dealership_id
 * @property int $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 * @mixin DealershipTranslation
 */
class Schedule extends Model
{
    const MONDAY = 'mon';
    const TUESDAY = 'tue';
    const WEDNESDAY = 'wed';
    const THURSDAY = 'thu';
    const FRIDAY = 'fri';
    const SATURDAY = 'sat';
    const SUNDAY = 'sun';

    const TYPE_SERVICE = 1; // график раб. для сервиса
    const TYPE_SALON   = 2; // график раб. для автосалона

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealership_schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealership_id',
        'day',
        'work_start',
        'work_end',
        'break_start',
        'break_end',
        'type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    public static function daysForSchedule()
    {
        return [
            self::MONDAY,
            self::TUESDAY,
            self::WEDNESDAY,
            self::THURSDAY,
            self::FRIDAY,
            self::SATURDAY,
            self::SUNDAY,
        ];
    }

    public static function daysForScheduleNumber($day)
    {
        $days = [
            self::MONDAY => 1,
            self::TUESDAY => 2,
            self::WEDNESDAY => 3,
            self::THURSDAY => 4,
            self::FRIDAY => 5,
            self::SATURDAY => 6,
            self::SUNDAY => 7,
        ];

        return $days[$day];
    }
}




