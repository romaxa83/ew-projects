<?php

namespace Database\Factories\Content\OurCases;

use App\Models\Content\OurCases\OurCase;
use App\Models\Content\OurCases\OurCaseCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|OurCase[]|OurCase create(array $attributes = [])
 */
class OurCaseFactory extends Factory
{
    protected $model = OurCase::class;

    public function definition(): array
    {
        return [
            'our_case_category_id' => OurCaseCategory::factory(),
            'active' => true,
        ];
    }
}
