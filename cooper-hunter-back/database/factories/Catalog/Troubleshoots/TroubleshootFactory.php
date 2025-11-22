<?php

namespace Database\Factories\Catalog\Troubleshoots;

use App\Models\Catalog\Troubleshoots\Group;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Troubleshoot|Troubleshoot[]|Collection create(array $attrs = [])
 */
class TroubleshootFactory extends BaseFactory
{
    protected $model = Troubleshoot::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'sort' => 1,
            'name' => $this->faker->name,
            'group_id' => Group::factory(),
        ];
    }

    public function disabled(): static
    {
        return $this->state(['active' => false]);
    }
}
