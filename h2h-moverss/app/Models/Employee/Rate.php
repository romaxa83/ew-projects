<?php

namespace App\Models\Employee;

use App\Models\Division;
use App\Models\Employee;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property int employee_id
 * @property int employee_name
 * @property int role_id
 * @property int role_name
 * @property int division_id
 * @property float workday
 * @property float holiday
 * @property float peakday
 * @property string season
 *
 * @see self::division()
 * @property Division|HasOne division
 *
 * @see self::role()
 * @property Role|HasOne role
 *
 * @see self::employee()
 * @property Employee|HasOne employee
 *
 * @mixin \Eloquent
 */
class Rate extends Model
{
    public const TABLE = 'employee_rates';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'employee_name',
        'role_id',
        'role_name',
        'division_id',
        'season',
        'workday',
        'holiday',
        'peakday',
    ];

    public function division(): HasOne
    {
        return $this->hasOne(Division::class, 'id', 'division_id');
    }

    public function role(): HasOne
    {
        return $this->hasOne(Role::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * type = 1 - holiday day
     * type = 2 - peak day
     * type = 3 - work day
     */
    public function getRateByDayType(int $type): float
    {
        if($type == 1){
            return (float)$this->holiday;
        }
        if($type == 2){
            return (float)$this->peakday;
        }

        return (float)$this->workday;
    }
}
