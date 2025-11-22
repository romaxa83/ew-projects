<?php

namespace Database\Factories\Commercial;

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectAddition;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|CommercialProjectAddition[]|CommercialProjectAddition create(array $attributes = [])
 */
class CommercialProjectAdditionFactory extends BaseFactory
{
    protected $model = CommercialProjectAddition::class;

    public function definition(): array
    {
        return [
            'commercial_project_id' => CommercialProject::factory(),
            'purchase_date' => now(),
            'installation_date' => now(),
            'installer_license_number' => $this->faker->postcode,
            'purchase_place' => $this->faker->sentence,
        ];
    }
}
