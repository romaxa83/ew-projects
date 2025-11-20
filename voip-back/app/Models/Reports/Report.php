<?php

namespace App\Models\Reports;

use App\Enums\Reports\ReportStatus;
use App\Filters\Reports\ReportFilter;
use App\Models\BaseModel;
use App\Models\Employees\Employee;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Reports\ReportFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property int employee_id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see Report::getCallTotalAttribute()
 * @property-read int call_total
 *
 * @see Report::items()
 * @property-read Collection|Item[] items
 *
 * @see Report::pauseItems()
 * @property-read Collection|PauseItem[] pauseItems
 *
 * @see Report::employee()
 * @property-read Employee employee
 *
 * @see Report::employeeWithTrashed()
 * @property-read Employee employeeWithTrashed
 *
 * @method static ReportFactory factory(int $number = null)
 */
class Report extends BaseModel
{
    use HasFactory;
    use Filterable;

    protected $table = self::TABLE;
    public const TABLE = 'reports';

    protected $appends = [
        'total',
    ];

    protected $with = ['items'];

    protected $fillable = [];

    protected $dates = [];

    protected $casts = [];

    public function modelFilter(): string
    {
        return ReportFilter::class;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeWithTrashed(): BelongsTo
    {
        return $this->employee()->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function pauseItems(): HasMany
    {
        return $this->hasMany(PauseItem::class);
    }

    public function getCallsCount(): int
    {
        return $this->items->count();
    }

    public function getPauseCount(): int
    {
        return $this->pauseItems->count();
    }

    public function getTotalAttribute(): int
    {
        return $this->getCallsCount();
    }

    public function getAnsweredCallsCount(): int
    {
        return $this->items->where('status', ReportStatus::ANSWERED)->count();
    }

    public function getDroppedCallsCount(): int
    {
        return $this->items->where('status', ReportStatus::NO_ANSWER)->count();
    }

    public function getTransferCallsCount(): int
    {
        return $this->items->where('status', ReportStatus::TRANSFER)->count();
    }

    public function getTotalWait(): int
    {
        return $this->items->sum(fn(Item $i):int => $i->wait);
    }

    public function getTotalTime(): int
    {
        return $this->items->sum(fn(Item $i):int => $i->total_time);
    }

    public function getTotalPauseTime(): int
    {
        return $this->pauseItems->sum(fn(PauseItem $i):int => $i->getDiffAtBySec());
    }
}
