<?php

namespace Database\Factories\Locations;

use App\Models\Locations\State;
use App\Models\Locations\StateTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|State[]|State create(array $attributes = [])
 */
class StateFactory extends Factory
{
    protected $model = State::class;

    public function definition(): array
    {
        return [
            'short_name' => $this->faker->lexify('?????'),
            'status' => true,
            'hvac_license' => $bool = $this->faker->boolean,
            'epa_license' => !$bool,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function hvac(): self
    {
        return $this->state(
            [
                'hvac_license' => true,
                'epa_license' => false,
            ]
        );
    }

    public function epa(): self
    {
        return $this->state(
            [
                'hvac_license' => false,
                'epa_license' => true,
            ]
        );
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (State $state) {
                foreach (languages() as $language) {
                    StateTranslation::factory()->create(
                        [
                            'row_id' => $state->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }
}
