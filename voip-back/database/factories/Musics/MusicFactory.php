<?php

namespace Database\Factories\Musics;

use App\Models\Departments\Department;
use App\Models\Musics\Music;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Music[]|Music create(array $attributes = [])
 */
class MusicFactory extends BaseFactory
{
    protected $model = Music::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'interval' => 20,
            'active' => true,
            'unhold_data' => [],
            'has_unhold_data' => false,
        ];
    }
}

