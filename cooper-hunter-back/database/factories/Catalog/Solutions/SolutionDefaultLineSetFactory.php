<?php

namespace Database\Factories\Catalog\Solutions;

use App\Models\Catalog\Solutions\SolutionDefaultLineSet;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|SolutionDefaultLineSet[]|SolutionDefaultLineSet create(array $attributes = [])
 */
class SolutionDefaultLineSetFactory extends BaseFactory
{
    protected $model = SolutionDefaultLineSet::class;

    public function definition(): array
    {
        return [
            //
        ];
    }
}
