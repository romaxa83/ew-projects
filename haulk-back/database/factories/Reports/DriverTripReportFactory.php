<?php

namespace Database\Factories\Reports;

use App\Models\Reports\DriverTripReport;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method DriverTripReport|DriverTripReport[]|Collection create($attributes = [], ?Model $parent = null)
 */
class DriverTripReportFactory extends Factory
{

    protected $model = DriverTripReport::class;

    public function definition(): array
    {
        return [
            'driver_id' => User::factory(),
            'report_date' => Carbon::now(),
            'date_from' => Carbon::now()->subDays(100),
            'date_to' => Carbon::now()->addDays(50),
        ];
    }
}
