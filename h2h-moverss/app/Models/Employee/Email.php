<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Employee\Email
 *
 * @property int $id
 * @property int $employee_id
 * @property int $is_primary
 * @property int $sort
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Email newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Email newQuery()
 * @method static \Illuminate\Database\Query\Builder|Email onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Email query()
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Email withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Email withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class Email extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'employees_emails';
    protected $dates = ['deleted_at'];
    protected $fillable = ['value', 'is_primary'];

}
