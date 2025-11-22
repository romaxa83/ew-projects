<?php

namespace Database\Factories\Projects;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|System[]|System create(array $attributes = [])
 */
class SystemFactory extends Factory
{
    protected $model = System::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'warranty_status' => WarrantyStatus::WARRANTY_NOT_REGISTERED(),
            'name' => $this->faker->company,
            'description' => $this->faker->sentence(10),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function onWarranty(): self
    {
        return $this->state(
            [
                'warranty_status' => WarrantyStatus::ON_WARRANTY()
            ]
        );
    }
}
