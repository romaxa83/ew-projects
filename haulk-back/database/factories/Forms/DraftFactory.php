<?php

namespace Database\Factories\Forms;

use App\Models\Forms\Draft;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Draft|Draft[]|Collection create($attributes = [], ?Model $parent = null)
 */
class DraftFactory extends Factory
{

    protected $model = Draft::class;

    public function definition(): array
    {
        return [
            'path' => 'some path',
            'user_id' => User::factory(),
            'body' => [
                'field1' => $this->faker->sentence,
                'field2' => $this->faker->sentence,
                'text' => $this->faker->text(2000),
            ],
        ];
    }
}
