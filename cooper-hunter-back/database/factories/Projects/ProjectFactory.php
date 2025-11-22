<?php

namespace Database\Factories\Projects;

use App\Models\Projects\Project;
use App\Models\Users\User;
use Database\Factories\ForMemberTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Project[]|Project create(array $attributes = [])
 */
class ProjectFactory extends Factory
{
    use ForMemberTrait;

    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'member_id' => User::factory(),
            'member_type' => User::MORPH_NAME,
            'name' => $this->faker->sentence(2),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
