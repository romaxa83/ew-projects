<?php

namespace App\Models\Schedules;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Schedules\AdditionsDayFactory;

/**
 * @property int id
 * @property int schedule_id
 * @property Carbon|null start_at
 * @property Carbon|null end_at
 *
 * @method static AdditionsDayFactory factory(int $number = null)
 */
class AdditionsDay extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = self::TABLE;
    public const TABLE = 'schedule_additions';

    protected $fillable = [];

    protected $dates = [
        'start_at',
        'end_at'
    ];

    protected $casts = [];
}
