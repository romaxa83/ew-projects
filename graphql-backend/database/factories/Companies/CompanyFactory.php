<?php

namespace Database\Factories\Companies;

use App\Models\Companies\Company;
use App\Models\Localization\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Company|Company[]|Collection create(array $attributes = [])
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        /** @var Language $language */
        $language = Language::inRandomOrder()->first();

        return [
            'name' => $this->faker->words($this->faker->numberBetween(1, 4), true),
            'lang' => $language->slug,
        ];
    }
}
