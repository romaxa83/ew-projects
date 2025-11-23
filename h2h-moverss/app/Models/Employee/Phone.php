<?php

namespace App\Models\Employee;

use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{SoftDeletes, Model};

/**
 * App\Models\Employee\Phone
 *
 * @property int $id
 * @property int $employee_id
 * @property int $type_id
 * @property int $is_primary
 * @property int $sort
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Phone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Phone newQuery()
 * @method static \Illuminate\Database\Query\Builder|Phone onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Phone query()
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Phone withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Phone withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class Phone extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'employees_phones';
    protected $dates = ['deleted_at'];
    protected $fillable = ['value', 'type_id', 'is_primary', 'sort'];

}
