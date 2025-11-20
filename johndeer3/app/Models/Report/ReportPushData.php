<?php

namespace App\Models\Report;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $report_id
 * @property boolean $is_send_start_day
 * @property boolean $is_send_end_day
 * @property boolean $is_send_week
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $planned_at
 * @property \Illuminate\Support\Carbon|null $prev_planned_at
 *
 * @property-read  Report|null $report
 */

class ReportPushData extends Model
{
    use HasFactory;

    protected $table = 'reports_push_data';

    protected $casts = [
        'is_send_start_day' => 'boolean',
        'is_send_end_day' => 'boolean',
        'is_send_week' => 'boolean',
    ];

    protected $dates = [
        'planned_at',
        'prev_planned_at'
    ];

    public function equalsPlannedDate(Carbon $date): bool
    {
        return $this->planned_at->eq($date);
    }

    // relation

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    // setter

    public function setSendStartDay()
    {
        $this->is_send_start_day = true;
        $this->save();
    }

    public function setSendEndDay()
    {
        $this->is_send_end_day = true;
        $this->save();
    }

    public function setSendWeek()
    {
        $this->is_send_week = true;
        $this->save();
    }
}

