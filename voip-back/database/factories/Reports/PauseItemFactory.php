<?php

namespace Database\Factories\Reports;

use App\Models\Reports;
use App\Models\Reports\Report;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Reports\PauseItem[]|Reports\PauseItem create(array $attributes = [])
 */
class PauseItemFactory extends BaseFactory
{
    protected $model = Reports\PauseItem::class;

    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'pause_at' => CarbonImmutable::now(),
            'unpause_at' => CarbonImmutable::now()->addHour(),
            'data' => [],
        ];
    }
}
