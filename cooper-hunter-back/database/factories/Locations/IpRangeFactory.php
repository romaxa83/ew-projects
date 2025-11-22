<?php

namespace Database\Factories\Locations;

use App\Models\Locations\IpRange;
use App\ValueObjects\Point;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|IpRange[]|IpRange create(array $attributes = [])
 */
class IpRangeFactory extends BaseFactory
{
    protected $model = IpRange::class;

    public function definition(): array
    {
        return [
            'ip_from' => ip2long('127.0.0.1'),
            'ip_to' => ip2long('127.0.0.255'),
            'state' => $this->faker->country,
            'city' => $this->faker->city,
            'coordinates' => new Point($this->faker->longitude, $this->faker->latitude),
            'zip' => $this->faker->postcode,
        ];
    }
}
