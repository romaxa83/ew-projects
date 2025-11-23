<?php

namespace Database\Factories\Materials;

use App\Models\Division;
use App\Models\Material;
use App\Models\Material\Group;
use Database\Factories\BaseFactory;

class MaterialFactory extends BaseFactory
{
    protected $model = Material::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'sort' => 1,
            'division_id' => Division::factory(),
            'group_id' => Group::factory(),
            'title' => $this->faker->title(),
            'notes' => null,
            'price' => 10,
            'need_packing' => 1,
            'need_unpacking' => 0,
            'packing_price' => 2.0,
            'unpacking_price' => 3.0,
            'deleted_at' => null,
        ];
    }
}
