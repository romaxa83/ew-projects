<?php

namespace Database\Factories\Orders;

use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Models\Orders\OrderShipping;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|OrderShipping[]|OrderShipping create(array $attributes = [])
 */
class OrderShippingFactory extends Factory
{
    protected $model = OrderShipping::class;

    public function definition(): array
    {
        $state = State::first();
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => new Phone($this->faker->phoneNumber),
            'address_first_line' => $this->faker->streetAddress,
            'address_second_line' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'country_id' => $state->country_id,
            'state_id' => $state->id,
            'zip' => (string)$this->faker->randomNumber(5),
            'order_delivery_type_id' => OrderDeliveryType::query()
                ->first()->id
        ];
    }
}
