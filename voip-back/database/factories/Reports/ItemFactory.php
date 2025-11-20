<?php

namespace Database\Factories\Reports;

use App\Enums\Calls\HistoryStatus;
use App\Enums\Reports\ReportStatus;
use App\Models\Reports;
use App\Models\Reports\Report;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Reports\Item[]|Reports\Item create(array $attributes = [])
 */
class ItemFactory extends BaseFactory
{
    protected $model = Reports\Item::class;

    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'status' => ReportStatus::ANSWERED,
            'num' => $this->faker->phoneNumber,
            'name' => $this->faker->name,
            'wait' => $this->faker->numberBetween(1, 100),
            'total_time' => $this->faker->numberBetween(1, 100),
            'call_at' => CarbonImmutable::now(),
        ];
    }
}

