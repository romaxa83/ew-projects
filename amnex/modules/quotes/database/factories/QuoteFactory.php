<?php

namespace Wezom\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Quotes\Enums\ContainerDimensionTypeEnum;
use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;
use Wezom\Users\Database\Factories\UserFactory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new(),
            'status' => QuoteStatusEnum::NEW,
            'container_number' => fake()->unique()->safeEmail(),
            'container_type' => ContainerDimensionTypeEnum::FT20,
            'is_not_standard_dimension' => false,
            'is_transload' => false,
            'is_palletized' => false,
            'number_pallets' => 1,
            'days_stored' => 1,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'pickup_terminal_id' => TerminalFactory::new(),
            'delivery_address' => 'Google DC, 25 Massachusetts Ave NW, Washington, DC 20001, USA',
            'piece_count' => 0,
            'user_name' => null,
        ];
    }
}
