<?php

namespace Database\Factories\Faq;

use App\Models\Faq\Faq;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Faq[]|Faq create(array $attributes = [])
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'active' => 1,
            'sort' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
