<?php

namespace App\Models\Employee;

use Carbon\CarbonImmutable;
use Database\Factories\Employees\SalesPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int employee_id
 * @property int|null local
 * @property int|null intrestate
 * @property int|null date_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static SalesPlanFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class SalesPlan extends Model
{
    use HasFactory;

    public const TABLE = 'employee_sales_plan';
    protected $table = self::TABLE;

    protected $fillable = [
        'employee_id',
        'local',
        'intrestate',
        'date_at'
    ];

    protected static function newFactory(): SalesPlanFactory
    {
        return SalesPlanFactory::new();
    }

    public function getDate(): string
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->date_at)
            ->format('Y-m');
    }
}
