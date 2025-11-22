<?php

namespace Database\Factories\Security;

use App\Models\Security\IpAccess;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method IpAccess|IpAccess[]|Collection create(array $attrs = [])
 */
class IpAccessFactory extends BaseFactory
{
    protected $model = IpAccess::class;

    public function definition(): array
    {
        return [
            'address' => $this->faker->ipv4,
            'description' => $this->faker->sentence,
            'active' => IpAccess::ACTIVE,
        ];
    }

    public function disabled(): static
    {
        return $this->state(['active' => false]);
    }
}
