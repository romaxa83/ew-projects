<?php

namespace App\Models\Employee;

use Carbon\CarbonImmutable;
use Database\Factories\Employees\EfficiencyPlanFactory;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int|null conversion_local_team
 * @property int|null conversion_long_team
 * @property int|null date_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static EfficiencyPlanFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class EfficiencyPlan extends Model
{
    use HasFactory;

    public const TABLE = 'employee_efficiency_plan';
    protected $table = self::TABLE;

    protected $fillable = [
        'conversion_local_team',
        'conversion_long_team',
        'date_at'
    ];

    protected static function newFactory(): EfficiencyPlanFactory
    {
        return EfficiencyPlanFactory::new();
    }

    public static function validDate(string $date): string
    {
        $d = DateTime::createFromFormat('Y-m', $date);
        if($d && $d->format('Y-m') == $date){
            return $date . '-01';
        }

        return $date;
    }

    public function getDate(): string
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->date_at)
            ->format('Y-m');
    }
}
