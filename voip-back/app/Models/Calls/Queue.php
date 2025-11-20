<?php

namespace App\Models\Calls;

use App\Enums\Calls\QueueStatus;
use App\Enums\Calls\QueueType;
use App\Filters\Calls\QueueFilter;
use App\Models\BaseModel;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Calls\QueueFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int|null department_id
 * @property int|null employee_id
 * @property QueueStatus status
 * @property string caller_num
 * @property string|null caller_name
 * @property string|null connected_num
 * @property string|null connected_name
 * @property int position
 * @property int wait
 * @property string|null serial_number
 * @property string|null case_id
 * @property string|null comment
 * @property string channel
 * @property string uniqueid
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon|null connected_at
 * @property Carbon|null called_at
 * @property int in_call
 * @property QueueType type
 *
 * @see Queue::department()
 * @property-read Department department
 *
 * @see Queue::employee()
 * @property-read Employee employee
 *
 * @method static QueueFactory factory(int $number = null)
 */
class Queue extends BaseModel
{
    use HasFactory;
    use Filterable;

    protected $table = self::TABLE;
    public const TABLE = 'call_queues';

    public const UNKNOWN = 'unknown';

    protected $fillable = [
        'status',
        'type',
        'connected_at',
        'called_at',
        'employee_id',
        'called_at',
        'caller_num',
        'caller_name',
        'connected_num',
        'connected_name',
        'in_call'
    ];

    protected $dates = [
        'connected_at',
        'called_at',
    ];

    protected $casts = [
        'status' => QueueStatus::class,
        'type' => QueueType::class,
    ];

    public function modelFilter(): string
    {
        return QueueFilter::class;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getConnectedNum(): ?string
    {
        if($this->connected_num === self::UNKNOWN){
            return null;
        }
        return $this->connected_num;
    }

    public function getCallerNum(): ?string
    {
        if($this->caller_num === self::UNKNOWN){
            return null;
        }
        return $this->caller_num;
    }

    public function getCallerName(): ?string
    {
        if($this->caller_name === self::UNKNOWN){
            return null;
        }
        return $this->caller_name;
    }

    public function getConnectedName(): ?string
    {
        if($this->connected_name === self::UNKNOWN){
            return null;
        }
        return $this->connected_name;
    }
}

