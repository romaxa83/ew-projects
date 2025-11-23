<?php

namespace App\Models\Employee;

use App\Models\Employee;
use Database\Factories\Employees\PbxDataFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne, Relations\Pivot};
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Employee\PbxData
 *
 * @property int id
 * @property int employee_id
 * @property int|null pbx_id
 * @property int|null pbx_ext
 * @property bool sip_status
 * @property int|null call_rec_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData query()
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData wherePbxExt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData wherePbxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData whereUpdatedAt($value)
 * @property int $pbx_show_webrtc
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData wherePbxShowWebrtc($value)
 * @property string|null $pbx_password
 * @method static \Illuminate\Database\Eloquent\Builder|PbxData wherePbxPassword($value)
 * @mixin \Eloquent
 * @method static PbxDataFactory factory(...$parameters)
 */
class PbxData extends Model implements Auditable
{
    use AuditableTrait;
    use \Awobaz\Compoships\Compoships;
    use HasFactory;

    public const TABLE = 'employees_pbx_data';
    protected $table = self::TABLE;

    protected $fillable = [
        'pbx_ext',
        'pbx_password',
        'pbx_id',
        'pbx_show_webrtc',
        'sip_status',
        'call_rec_id'
    ];

    protected $casts = [
        'sip_status' => 'boolean',
    ];

    protected static function newFactory(): PbxDataFactory
    {
        return PbxDataFactory::new();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
