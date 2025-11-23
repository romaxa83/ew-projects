<?php

namespace App\Models\Employee;

use App\Models\Client\MessengerType;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{SoftDeletes, Model};

/**
 * App\Models\Employee\Messenger
 *
 * @property int $id
 * @property int $employee_id
 * @property string $value
 * @property int $type_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read MessengerType|null $type
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger newQuery()
 * @method static \Illuminate\Database\Query\Builder|Messenger onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger query()
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Messenger withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Messenger withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class Messenger extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'employees_messengers';
    protected $dates = ['deleted_at'];
    protected $fillable = ['value', 'type_id'];
    protected $with = ['type:id,icon'];

    public function type()
    {
        return $this->hasOne(MessengerType::class, 'id', 'type_id');
    }

}
