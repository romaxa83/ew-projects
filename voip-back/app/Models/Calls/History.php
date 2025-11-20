<?php

namespace App\Models\Calls;

use App\Enums\Calls\HistoryStatus;
use App\Enums\Formats\DatetimeEnum;
use App\Filters\Calls\HistoryFilter;
use App\Models\BaseModel;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Calls\HistoryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int|null employee_id       // связь на агента, который принимал звонок (основной кейс)
 * @property int|null from_employee_id  // связь на агента, если он был инициатором звонко, используется для отображения истории данного агента
 * @property int|null department_id
 * @property HistoryStatus status
 * @property string from_num
 * @property string from_name
 * @property string from_name_pretty
 * @property string dialed
 * @property string|null dialed_name
 * @property int duration
 * @property int billsec
 * @property string|null serial_numbers
 * @property string|null case_id
 * @property string|null comment
 * @property string lastapp
 * @property string uniqueid
 * @property string channel
 * @property Carbon|null call_date
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see History::department()
 * @property-read Department department
 *
 * @see History::employee()
 * @property-read Employee employee
 *
 * @see History::queue()
 * @property-read Queue|null queue
 *
 * @method static HistoryFactory factory(int $number = null)
 */
class History extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const UNKNOWN = 'unknown';

    protected $table = self::TABLE;
    public const TABLE = 'call_histories';

    protected $fillable = [];

    public const ALLOWED_SORTING_FIELDS = [
        'call_date',
    ];

    protected $dates = [
        'call_date'
    ];

    protected $casts = [
        'status' => HistoryStatus::class,
    ];

    public function modelFilter(): string
    {
        return HistoryFilter::class;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function queue()
    {
        return $this->belongsTo(Queue::class, 'channel', 'channel');
    }

    public function getUrlAudioRecord(): ?string
    {
        if($this->status->isAnswered() || $this->status->isTransfer()){
            $baseUrl = config('asterisk.call_record_url');

            return "{$baseUrl}/{$this->call_date->format(DatetimeEnum::DATE_SLASH)}/{$this->uniqueid}.mp3";
        }

        return null;
    }

    public function getFromNumber(): ?string
    {
        if($this->from_num === ''){
            return null;
        }
        return $this->from_num;
    }

    public function getFromName(): ?string
    {
        if($this->from_name_pretty === self::UNKNOWN){
            return null;
        }
        return $this->from_name_pretty;
    }

    public function getDialed(): ?string
    {
        if($this->dialed === ''){
            return null;
        }
        return $this->dialed;
    }

    public function getDialedName(): ?string
    {
        if($this->dialed == 'admin'){
            return null;
        }
        return $this->dialed_name;
    }
}
