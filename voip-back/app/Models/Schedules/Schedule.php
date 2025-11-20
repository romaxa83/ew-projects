<?php

namespace App\Models\Schedules;

use App\GraphQL\Types\Schedules\AdditionDay;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveTrait;
use Carbon\Carbon;
use Database\Factories\Schedules\ScheduleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see Schedule::days()
 * @property-read Collection|WorkDay[] days
 *
 * @see Schedule::additionalDays()
 * @property-read Collection|AdditionDay[] additionalDays
 *
 * @method static ScheduleFactory factory(int $number = null)
 */
class Schedule extends BaseModel
{
    use HasFactory;
    use ActiveTrait;

    protected $table = self::TABLE;
    public const TABLE = 'schedules';

    protected $fillable = [];

    protected $dates = [];

    protected $casts = [];

    public function days(): HasMany
    {
        return $this->hasMany(WorkDay::class)->orderBy('sort');
    }

    public function additionalDays(): HasMany
    {
        return $this->hasMany(AdditionsDay::class);
    }
}
