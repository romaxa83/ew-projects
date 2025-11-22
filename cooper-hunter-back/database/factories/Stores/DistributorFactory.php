<?php

namespace Database\Factories\Stores;

use App\Models\Locations\State;
use App\Models\Stores\Distributor;
use App\ValueObjects\Phone;
use App\ValueObjects\Point;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Distributor[]|Distributor create(array $attributes = [])
 */
class DistributorFactory extends BaseFactory
{
    protected $model = Distributor::class;

    public function definition(): array
    {
        return [
            'state_id' => State::factory(),
            'active' => true,
            'coordinates' => new Point($this->faker->longitude, $this->faker->latitude),
            'address' => $this->faker->address,
            'address_metaphone' => static fn(array $a) => makeSearchSlug($a['address']),
            'link' => $this->faker->imageUrl,
            'phone' => new Phone($this->faker->e164PhoneNumber),
        ];
    }

    public function coordinates(Point $point): self
    {
        return $this->state(
            [
                'coordinates' => $point
            ]
        );
    }
}
