<?php

namespace Database\Factories\Catalog\Troubleshoots;

use App\Models\Catalog\Troubleshoots\Group;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Group|Group[]|Collection create(array $attrs = [])
 */
class GroupFactory extends BaseFactory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'sort' => 1,
        ];
    }

    public function disabled(): static
    {
        return $this->state(['active' => false]);
    }
}

