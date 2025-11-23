<?php

namespace App\Models\Employee;

use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{SoftDeletes, Model};

/**
 * App\Models\Employee\Notes
 *
 * @property int $id
 * @property int $employee_id
 * @property int $user_id
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newQuery()
 * @method static \Illuminate\Database\Query\Builder|Notes onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Notes withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Notes withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class Notes extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'employees_notes';
    protected $dates = ['deleted_at'];
    protected $fillable = ['value', 'user_id'];

}
