<?php

namespace Database\Factories\Branches;

use App\Models\Branches\Branch;
use App\Models\Locations\Region;
use App\Models\Users\User;
use App\Traits\Factory\HasPhonesFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Branch[]|Branch create(array $attributes = [])
 */
class BranchFactory extends Factory
{
    use HasPhonesFactory;

    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'city' => $this->faker->city,
            'region_id' => Region::inRandomOrder()
                ->first()->id,
            'address' => $this->faker->streetAddress,
            'active' => true,
        ];
    }

    public function withEmployees(int $total = 5): self
    {
        return $this->afterCreating(
            fn(Branch $branch) => $branch->users()
                ->sync(
                    User::factory()
                        ->count($total)
                        ->create()
                        ->pluck('id')
                        ->toArray()
                )
        );
    }
}
