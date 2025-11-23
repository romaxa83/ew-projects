<?php

namespace App\Models;

use App\Models\Order\WorkDispatchTouch;
use Database\Factories\Employees\DispatchEmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\DispatchEmployer
 *
 * @property int $id
 * @property int $work_id
 * @property int $employer_id
 * @property mixed|null $miscs
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read WorkDispatchTouch $work
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchEmployer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchEmployer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchEmployer query()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchEmployer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchEmployer whereEmployerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchEmployer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchEmployer whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchEmployer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchEmployer whereWorkId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static DispatchEmployeeFactory factory(...$parameters)
 */
class DispatchEmployer extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'dispatch-employee';

    public const TABLE = 'dispatch_employees';
    protected $table = self::TABLE;

    protected $fillable = ['employer_id'];
    protected $touches = ['work'];
    protected $casts = [
        'miscs' => 'json',
    ];

    protected static function newFactory(): DispatchEmployeeFactory
    {
        return DispatchEmployeeFactory::new();
    }

    public function work()
    {
        return $this->belongsTo(WorkDispatchTouch::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'id', 'employer_id');
    }
}
