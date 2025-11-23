<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Employee\BusyWeekDay
 *
 * @property int $id
 * @property int $employee_id
 * @property int $user_id
 * @property mixed|null $miscs
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay query()
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class BusyWeekDay extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'employees_busy_w_days';
    protected $casts = [
        'miscs' => 'json',
    ];
    protected $fillable = ['user_id', 'miscs'];

}
